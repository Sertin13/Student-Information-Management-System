# Phase 9 safe API smoke/functional tests. Run in PowerShell 5.1+.
# Writes report CSV next to script. Only mutates a NEW, unique Student created during this run.
param(
    [string]$BaseUrl = 'http://127.0.0.1:8000/api/v1',
    [string]$Password = 'password'
)
$ErrorActionPreference = 'Stop'
$results = [System.Collections.Generic.List[object]]::new()
$base = $BaseUrl.TrimEnd('/')
$runId = [guid]::NewGuid().ToString('N').Substring(0,12)
$createdId = $null
$createdNumber = "PHASE9-$runId"
$adminHeaders = $null

function Record([string]$name,[string]$status,[string]$detail='') {
    $results.Add([pscustomobject]@{ Test=$name; Status=$status; Detail=$detail })
    Write-Host "[$status] $name $(if($detail){'- '+$detail})"
}
function Request([string]$method,[string]$path,[hashtable]$headers=$null,[object]$payload=$null) {
    $requestArgs = @{ Uri="$base/$path"; Method=$method; Headers=@{Accept='application/json'}; ErrorAction='Stop' }
    if($headers){foreach($key in $headers.Keys){$requestArgs.Headers[$key]=$headers[$key]}}
    if($null -ne $payload){$requestArgs.ContentType='application/json';$requestArgs.Body=($payload|ConvertTo-Json -Depth 12 -Compress)}
    try {
        $resp=Invoke-WebRequest @requestArgs -UseBasicParsing
        $json=$null
        if($resp.Content){try{$json=$resp.Content|ConvertFrom-Json}catch{}}
        return [pscustomobject]@{Status=[int]$resp.StatusCode;Data=$json}
    } catch {
        $code=0
        if($_.Exception.Response){$code=[int]$_.Exception.Response.StatusCode}
        return [pscustomobject]@{Status=$code;Data=$null}
    }
}
function Check([string]$name,[bool]$ok,[string]$detail='') {
    if($ok){Record $name 'PASS' $detail}else{Record $name 'FAIL' $detail}
}
function Login([string]$role,[string]$email) {
    $r=Request 'POST' 'auth/login' $null @{email=$email;password=$Password}
    if($r.Status -eq 200 -and $r.Data.success -and $r.Data.data.token){
        $h=@{Authorization="Bearer $($r.Data.data.token)"}
        $me=Request 'GET' 'auth/me' $h
        Check "$role login + /me" ($me.Status -eq 200 -and $me.Data.data.role.name -eq $role) "login=$($r.Status), me=$($me.Status)"
        return $h
    }
    Record "$role login + /me" 'FAIL' "HTTP $($r.Status)"
    return $null
}

Write-Host "Phase 9 run $runId - $base"
# /up is outside /api/v1, so check via direct request instead.
try {$up=Invoke-WebRequest -Uri ($base -replace '/api/v1$','/up') -UseBasicParsing -ErrorAction Stop; Check 'Laravel health' ($up.StatusCode -eq 200) "HTTP $($up.StatusCode)"}
catch {Record 'Laravel health' 'FAIL' $_.Exception.Message}
$anonymous=Request 'GET' 'students'
Check 'Anonymous blocked' ($anonymous.Status -eq 401) "HTTP $($anonymous.Status)"
$badLogin=Request 'POST' 'auth/login' $null @{email='admin@example.com';password='phase9-invalid-password'}
Check 'Invalid login blocked' ($badLogin.Status -eq 401) "HTTP $($badLogin.Status)"

$adminHeaders=Login 'Admin' 'admin@example.com'
$staffHeaders=Login 'Staff' 'staff@example.com'
$instructorHeaders=Login 'Instructor' 'instructor@example.com'
$studentHeaders=Login 'Student' 'student@example.com'
$modules=@('programs','courses','academic-terms','students','course-offerings','enrollments','grades')
foreach($role in @(@{Name='Admin';Headers=$adminHeaders},@{Name='Staff';Headers=$staffHeaders})) {
    if(-not $role.Headers){Record "$($role.Name) module reads" 'SKIP' 'Login failed';continue}
    foreach($path in $modules){$r=Request 'GET' $path $role.Headers;Check "$($role.Name) GET $path" ($r.Status -eq 200) "HTTP $($r.Status)"}
}
if($instructorHeaders){
    foreach($path in @('my/course-offerings','my/teaching-enrollments')){$r=Request 'GET' $path $instructorHeaders;Check "Instructor GET $path" ($r.Status -eq 200) "HTTP $($r.Status)"}
    foreach($path in $modules){$r=Request 'GET' $path $instructorHeaders;Check "Instructor forbidden $path" ($r.Status -eq 403) "HTTP $($r.Status)"}
} else {Record 'Instructor authorization' 'SKIP' 'Login failed'}
if($studentHeaders){
    $profile=Request 'GET' 'my/student' $studentHeaders
    Check 'Student own profile' ($profile.Status -eq 200 -and $profile.Data.data.id -gt 0) "HTTP $($profile.Status)"
    foreach($path in @('my/enrollments','my/grades')){$r=Request 'GET' $path $studentHeaders;Check "Student GET $path" ($r.Status -eq 200) "HTTP $($r.Status)"}
    if($profile.Status -eq 200 -and $profile.Data.data.id){
        $id=[int]$profile.Data.data.id
        $own=Request 'GET' "students/$id/academic-record" $studentHeaders
        Check 'Student own academic record' ($own.Status -eq 200) "HTTP $($own.Status)"
        $other=if($id -eq 2){1}else{2}
        $denied=Request 'GET' "students/$other/academic-record" $studentHeaders
        Check 'Student other academic record forbidden' ($denied.Status -eq 403) "HTTP $($denied.Status)"
    }
    foreach($path in @('students','programs','my/teaching-enrollments')){$r=Request 'GET' $path $studentHeaders;Check "Student forbidden $path" ($r.Status -eq 403) "HTTP $($r.Status)"}
} else {Record 'Student authorization' 'SKIP' 'Login failed'}

if($adminHeaders){
    $first=Request 'GET' 'students?per_page=1' $adminHeaders
    $page2=Request 'GET' 'students?page=2' $adminHeaders
    Check 'Student pagination' ($first.Status -eq 200 -and $page2.Status -eq 200 -and $page2.Data.data.current_page -eq 2) "page2 HTTP $($page2.Status)"
    if($first.Status -eq 200 -and @($first.Data.data.data).Count -gt 0){
        $s=$first.Data.data.data[0]
        $num=[uri]::EscapeDataString([string]$s.student_number)
        $search=Request 'GET' "students?search=$num" $adminHeaders
        Check 'Student number search' ($search.Status -eq 200 -and @($search.Data.data.data|Where-Object {$_.student_number -eq $s.student_number}).Count -gt 0)
        foreach($filter in @(@{Key='year_level';Value=$s.year_level},@{Key='status';Value=$s.status},@{Key='program_id';Value=$s.program_id})){
            $r=Request 'GET' "students?$($filter.Key)=$([uri]::EscapeDataString([string]$filter.Value))" $adminHeaders
            $bad=@($r.Data.data.data|Where-Object {$_.PSObject.Properties[$filter.Key].Value -ne $filter.Value})
            Check "Student filter $($filter.Key)" ($r.Status -eq 200 -and $r.Data.data.total -ge 1 -and $bad.Count -eq 0)
        }
        $dup=Request 'POST' 'students' $adminHeaders @{student_number=$s.student_number;first_name='Duplicate';last_name='Test';program_id=$s.program_id;year_level=1;status='Regular'}
        Check 'Student duplicate rejected' ($dup.Status -eq 422) "HTTP $($dup.Status)"
    }
    $sorted=Request 'GET' 'students?sort=student_number&direction=desc&per_page=100' $adminHeaders
    $nums=@($sorted.Data.data.data|ForEach-Object {$_.student_number})
    $expected=@($nums|Sort-Object -Descending)
    Check 'Student descending sort' ($sorted.Status -eq 200 -and (($nums -join '|') -ceq ($expected -join '|')))
    $size=Request 'GET' 'students?per_page=5' $adminHeaders
    Check 'Student page size' ($size.Status -eq 200 -and $size.Data.data.per_page -eq 5 -and @($size.Data.data.data).Count -eq 5)
    $invalid=Request 'POST' 'students' $adminHeaders @{student_number='';first_name='';last_name='';program_id=999999;year_level=99;status='InvalidStatus'}
    Check 'Student validation 422' ($invalid.Status -eq 422) "HTTP $($invalid.Status)"

    # Create only a fresh test student, and delete only after verifying its unique marker.
    $programs=Request 'GET' 'programs?per_page=1' $adminHeaders
    if($programs.Status -eq 200 -and @($programs.Data.data.data).Count -gt 0){
        $programId=$programs.Data.data.data[0].id
        $created=Request 'POST' 'students' $adminHeaders @{student_number=$createdNumber;first_name='PhaseNine';last_name='TemporaryTest';program_id=$programId;year_level=1;status='Regular'}
        if($created.Status -eq 201 -and $created.Data.data.student_number -eq $createdNumber){
            $createdId=[int]$created.Data.data.id
            Record 'Student CREATE' 'PASS' "ID $createdId"
            $read=Request 'GET' "students/$createdId" $adminHeaders
            Check 'Student READ' ($read.Status -eq 200 -and $read.Data.data.student_number -eq $createdNumber)
            $updated=Request 'PUT' "students/$createdId" $adminHeaders @{student_number=$createdNumber;first_name='PhaseNineUpdated';last_name='TemporaryTest';program_id=$programId;year_level=1;status='Regular'}
            $verified=Request 'GET' "students/$createdId" $adminHeaders
            Check 'Student UPDATE' ($updated.Status -eq 200 -and $verified.Data.data.first_name -eq 'PhaseNineUpdated') "HTTP $($updated.Status)"
            $beforeDelete=Request 'GET' "students/$createdId" $adminHeaders
            if($beforeDelete.Status -eq 200 -and $beforeDelete.Data.data.student_number -eq $createdNumber){
                $deleted=Request 'DELETE' "students/$createdId" $adminHeaders
                $after=Request 'GET' "students/$createdId" $adminHeaders
                Check 'Student DELETE' (($deleted.Status -eq 200 -or $deleted.Status -eq 204) -and $after.Status -eq 404) "DELETE $($deleted.Status), GET $($after.Status)"
                if($after.Status -eq 404){$createdId=$null}
            }else{Record 'Student DELETE' 'SKIP' 'Identity verification failed'}
        }else{Record 'Student CREATE' 'FAIL' "HTTP $($created.Status)"}
    }else{Record 'Student CRUD' 'SKIP' 'No valid Program available'}
}else{Record 'Student collection/CRUD' 'SKIP' 'Admin login failed'}

if($null -ne $createdId){Write-Warning "Temporary student $createdId ($createdNumber) may remain. No automatic retry or unrelated cleanup will be performed."}
$report=Join-Path $PSScriptRoot 'Phase9-Results.csv'
$results|Export-Csv -Path $report -NoTypeInformation -Encoding UTF8
Write-Host "`nRESULTS: PASS=$(@($results|Where-Object Status -eq 'PASS').Count) FAIL=$(@($results|Where-Object Status -eq 'FAIL').Count) SKIP=$(@($results|Where-Object Status -eq 'SKIP').Count)"
Write-Host "Report: $report"
Write-Host 'Note: Browser UX, live Instructor grade writes, and other modules CRUD are NOT tested by this script.'
