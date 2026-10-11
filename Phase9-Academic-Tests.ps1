# Phase 9 academic transaction tests - isolated temporary records only.
# Never resets or reseeds the database. Does not create grades (no DELETE endpoint).
param([string]$BaseUrl = 'http://127.0.0.1:8000/api/v1')
$ErrorActionPreference = 'Stop'
$runId = [guid]::NewGuid().ToString('N').Substring(0,12)
$results = [System.Collections.Generic.List[object]]::new()
$created = @{ student=$null; offering=$null; enrollment=$null }
$adminHeaders = $null
$instructorHeaders = $null
$studentHeaders = $null
function Log($name,$ok,$detail='') {
  $status = if($ok){'PASS'}else{'FAIL'}
  $results.Add([pscustomobject]@{Test=$name;Status=$status;Detail=[string]$detail})
  Write-Host "[$status] $name - $detail"
}
function CallApi($method,$path,$headers,$body=$null) {
  $args = @{ Uri="$BaseUrl/$path"; Method=$method; Headers=$headers; ErrorAction='Stop'; UseBasicParsing=$true }
  if($null -ne $body){$args.ContentType='application/json';$args.Body=($body | ConvertTo-Json -Depth 12 -Compress)}
  try {
    $response=Invoke-WebRequest @args
    $data=$null
    if($response.Content){$data=$response.Content | ConvertFrom-Json}
    return [pscustomobject]@{Status=[int]$response.StatusCode;Data=$data;Error=''}
  } catch {
    $status=0
    if($_.Exception.Response){$status=[int]$_.Exception.Response.StatusCode}
    $msg=$_.Exception.Message
    return [pscustomobject]@{Status=$status;Data=$null;Error=$msg}
  }
}
function Login($email) {
  $r=CallApi 'POST' 'auth/login' @{Accept='application/json'} @{email=$email;password='password'}
  if($r.Status -ne 200 -or -not $r.Data.data.token){throw "Login failed for $email (HTTP $($r.Status))"}
  return @{Authorization="Bearer $($r.Data.data.token)";Accept='application/json'}
}
function Verify($name,$method,$path,$headers,$expected,$body=$null) {
  $r=CallApi $method $path $headers $body
  Log $name ($r.Status -eq $expected) "HTTP $($r.Status), expected $expected"
  return $r
}
Write-Host "Phase 9 academic test run $runId - $BaseUrl"
try {
  $adminHeaders=Login 'admin@example.com'
  $instructorHeaders=Login 'instructor@example.com'
  $studentHeaders=Login 'student@example.com'
  Log 'Role authentication' $true 'Admin, Instructor, Student authenticated'
  $programs=CallApi 'GET' 'programs?per_page=1' $adminHeaders
  $courses=CallApi 'GET' 'courses?per_page=1' $adminHeaders
  $terms=CallApi 'GET' 'academic-terms?per_page=1' $adminHeaders
  $users=CallApi 'GET' 'auth/me' $instructorHeaders
  $programId=$programs.Data.data.data[0].id
  $courseId=$courses.Data.data.data[0].id
  $termId=$terms.Data.data.data[0].id
  $instructorId=$users.Data.data.id
  if(-not $programId -or -not $courseId -or -not $termId -or -not $instructorId){throw 'Could not resolve required IDs; no records created.'}
  Log 'Reference data' $true "Program=$programId Course=$courseId Term=$termId Instructor=$instructorId"
  $studentNumber="P9A-$runId"
  $s=CallApi 'POST' 'students' $adminHeaders @{student_number=$studentNumber;first_name='AcademicPhaseNine';last_name='Temporary';program_id=$programId;year_level=1;status='Regular'}
  if($s.Status -ne 201 -or -not $s.Data.data.id){throw "Temporary Student CREATE failed HTTP $($s.Status): $($s.Error)"}
  $created.student=[int]$s.Data.data.id
  Log 'Student CREATE' $true "ID $($created.student)"
  $section="P9-$runId"
  $o=CallApi 'POST' 'course-offerings' $adminHeaders @{course_id=$courseId;academic_term_id=$termId;instructor_id=$instructorId;section=$section;capacity=10;status='Active'}
  if($o.Status -ne 201 -or -not $o.Data.data.id){throw "Offering CREATE failed HTTP $($o.Status): $($o.Error)"}
  $created.offering=[int]$o.Data.data.id
  Log 'Offering CREATE' $true "ID $($created.offering)"
  $r=CallApi 'GET' "course-offerings/$($created.offering)" $adminHeaders
  Log 'Offering READ' ($r.Status -eq 200 -and $r.Data.data.section -eq $section) "HTTP $($r.Status)"
  $r=CallApi 'PUT' "course-offerings/$($created.offering)" $adminHeaders @{course_id=$courseId;academic_term_id=$termId;instructor_id=$instructorId;section=$section;capacity=15;status='Active'}
  $check=CallApi 'GET' "course-offerings/$($created.offering)" $adminHeaders
  Log 'Offering UPDATE' ($r.Status -eq 200 -and $check.Data.data.capacity -eq 15) "HTTP $($r.Status)"
  $r=CallApi 'GET' 'my/course-offerings?per_page=100' $instructorHeaders
  $assigned=@($r.Data.data.data | Where-Object {$_.id -eq $created.offering}).Count -gt 0
  Log 'Instructor assigned offering visible' ($r.Status -eq 200 -and $assigned) "HTTP $($r.Status)"
  $enrollmentDate='2026-10-09'
  $e=CallApi 'POST' 'enrollments' $adminHeaders @{student_id=$created.student;course_offering_id=$created.offering;enrollment_date=$enrollmentDate;status='Enrolled'}
  if($e.Status -ne 201 -or -not $e.Data.data.id){throw "Enrollment CREATE failed HTTP $($e.Status): $($e.Error)"}
  $created.enrollment=[int]$e.Data.data.id
  Log 'Enrollment CREATE' $true "ID $($created.enrollment)"
  $r=CallApi 'GET' "enrollments/$($created.enrollment)" $adminHeaders
  Log 'Enrollment READ' ($r.Status -eq 200 -and $r.Data.data.student_id -eq $created.student) "HTTP $($r.Status)"
  $r=CallApi 'PUT' "enrollments/$($created.enrollment)" $adminHeaders @{student_id=$created.student;course_offering_id=$created.offering;enrollment_date=$enrollmentDate;status='Completed'}
  $check=CallApi 'GET' "enrollments/$($created.enrollment)" $adminHeaders
  Log 'Enrollment UPDATE' ($r.Status -eq 200 -and $check.Data.data.status -eq 'Completed') "HTTP $($r.Status)"
  $r=CallApi 'GET' "students/$($created.student)/academic-record" $adminHeaders
  $record=@($r.Data.data.records | Where-Object {$_.id -eq $created.enrollment}).Count -gt 0
  Log 'Academic record contains enrollment' ($r.Status -eq 200 -and $record) "HTTP $($r.Status)"
  $r=CallApi 'GET' 'my/teaching-enrollments?per_page=100' $instructorHeaders
  Log 'Instructor roster endpoint' ($r.Status -eq 200) "HTTP $($r.Status)"
  $null=Verify 'Student forbidden offering edit' 'PUT' "course-offerings/$($created.offering)" $studentHeaders 403 @{course_id=$courseId;academic_term_id=$termId;section=$section;capacity=25;status='Active'}
  $null=Verify 'Instructor forbidden enrollment edit' 'PUT' "enrollments/$($created.enrollment)" $instructorHeaders 403 @{student_id=$created.student;course_offering_id=$created.offering;enrollment_date=$enrollmentDate;status='Dropped'}
  $null=Verify 'Instructor invalid grade rejected' 'POST' 'my/grades' $instructorHeaders 422 @{enrollment_id=$created.enrollment;grade=150;remarks='Invalid'}
  $null=Verify 'Student forbidden grade creation' 'POST' 'my/grades' $studentHeaders 403 @{enrollment_id=$created.enrollment;grade=90;remarks='Not allowed'}
  $null=Verify 'Instructor invalid grade update rejected' 'PUT' 'my/grades/999999999' $instructorHeaders 404 @{grade=150}
  Log 'Live grade create/update' $true 'SKIPPED intentionally: grade deletion API unavailable; no persistent grade created'
} catch {
  Log 'Test runner' $false $_.Exception.Message
} finally {
  # Cleanup in reverse dependency order. Only delete a record if its unique identity matches.
  if($adminHeaders){
    if($created.enrollment){
      $r=CallApi 'GET' "enrollments/$($created.enrollment)" $adminHeaders
      if($r.Status -eq 200 -and $r.Data.data.student_id -eq $created.student -and $r.Data.data.course_offering_id -eq $created.offering){
        $d=CallApi 'DELETE' "enrollments/$($created.enrollment)" $adminHeaders
        $v=CallApi 'GET' "enrollments/$($created.enrollment)" $adminHeaders
        Log 'Enrollment cleanup' ($d.Status -eq 204 -and $v.Status -eq 404) "DELETE $($d.Status), GET $($v.Status)"
      }else{Log 'Enrollment cleanup' $false 'Identity mismatch; deletion cancelled'}
    }
    if($created.offering){
      $r=CallApi 'GET' "course-offerings/$($created.offering)" $adminHeaders
      if($r.Status -eq 200 -and $r.Data.data.section -eq $section -and $r.Data.data.course_id -eq $courseId){
        $d=CallApi 'DELETE' "course-offerings/$($created.offering)" $adminHeaders
        $v=CallApi 'GET' "course-offerings/$($created.offering)" $adminHeaders
        Log 'Offering cleanup' ($d.Status -eq 204 -and $v.Status -eq 404) "DELETE $($d.Status), GET $($v.Status)"
      }else{Log 'Offering cleanup' $false 'Identity mismatch; deletion cancelled'}
    }
    if($created.student){
      $r=CallApi 'GET' "students/$($created.student)" $adminHeaders
      if($r.Status -eq 200 -and $r.Data.data.student_number -eq $studentNumber){
        $d=CallApi 'DELETE' "students/$($created.student)" $adminHeaders
        $v=CallApi 'GET' "students/$($created.student)" $adminHeaders
        Log 'Student cleanup' ($d.Status -eq 204 -and $v.Status -eq 404) "DELETE $($d.Status), GET $($v.Status)"
      }else{Log 'Student cleanup' $false 'Identity mismatch; deletion cancelled'}
    }
  }
  $out=Join-Path (Get-Location) "Phase9-Academic-Results-$runId.csv"
  $results | Export-Csv -Path $out -NoTypeInformation -Encoding UTF8
  $pass=@($results | Where-Object Status -eq 'PASS').Count
  $fail=@($results | Where-Object Status -eq 'FAIL').Count
  Write-Host "RESULTS: PASS=$pass FAIL=$fail"
  Write-Host "CSV: $out"
  if($fail -gt 0){Write-Host 'Review failures and any cleanup warnings. Do not rerun until leftover temporary records are checked.'}
}
