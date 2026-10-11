import { useEffect, useState } from 'react'
import { ReferenceDataPage } from '../components/ReferenceDataPage.jsx'
import service from '../services/gradeService.js'
import enrollmentService from '../services/enrollmentService.js'
import { useAuth } from '../context/authContext.js'
import { InstructorGradeRoster } from '../components/InstructorGradeRoster.jsx'
export function GradesPage(){const {hasRole}=useAuth();const [enrollments,setEnrollments]=useState([]);useEffect(()=>{if(!hasRole('Instructor'))enrollmentService.list(1,{without_grade:1}).then((page)=>setEnrollments(page.data??[]))},[hasRole]);if(hasRole('Instructor'))return <InstructorGradeRoster/>;const ungraded=enrollments;const fields=[{name:'enrollment_id',label:'Enrollment',type:'select',options:ungraded.map(x=>({value:String(x.id),label:`${x.student?.student_number??''} — ${x.student?.first_name??''} ${x.student?.last_name??''} — ${x.course_offering?.course?.course_code??''} — Enrollment #${x.id}`})),required:true},{name:'grade',label:'Grade',type:'number'},{name:'remarks',label:'Remarks'}];return <ReferenceDataPage title="Grades" service={service} fields={fields} columns={[{name:'enrollment_id',label:'Enrollment ID'},{name:'grade',label:'Grade'},{name:'remarks',label:'Remarks'}]} queryConfig={{sortOptions:[{value:'grade',label:'Grade'},{value:'created_at',label:'Created date'}]} } canAdd={ungraded.length>0}/>}
