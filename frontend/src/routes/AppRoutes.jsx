import { Navigate, Route, Routes } from 'react-router-dom'
import { AppLayout } from '../layouts/AppLayout.jsx'
import { LoginPage } from '../pages/LoginPage.jsx'
import { DashboardPage } from '../pages/DashboardPage.jsx'
import { PlaceholderPage } from '../pages/PlaceholderPage.jsx'
import { ForbiddenPage, NotFoundPage } from '../pages/ErrorPages.jsx'
import { ProtectedRoute } from './ProtectedRoute.jsx'
import { ProgramsPage } from '../pages/ProgramsPage.jsx'
import { CoursesPage } from '../pages/CoursesPage.jsx'
import { AcademicTermsPage } from '../pages/AcademicTermsPage.jsx'
import { StudentsPage } from '../pages/StudentsPage.jsx'
import { StudentDetailsPage } from '../pages/StudentDetailsPage.jsx'
import { CourseOfferingsPage } from '../pages/CourseOfferingsPage.jsx'
import { EnrollmentsPage } from '../pages/EnrollmentsPage.jsx'
import { GradesPage } from '../pages/GradesPage.jsx'
import { AcademicRecordPage } from '../pages/AcademicRecordPage.jsx'
import { ProfilePage } from '../pages/ProfilePage.jsx'

const pages = [['dashboard', 'Dashboard'], ['students', 'Students'], ['programs', 'Programs'], ['courses', 'Courses'], ['academic-terms', 'Academic Terms'], ['course-offerings', 'Course Offerings'], ['enrollments', 'Enrollments'], ['grades', 'Grades'], ['academic-record', 'Academic Record'], ['profile', 'Profile']]

export function AppRoutes() {
  return <Routes><Route path="/login" element={<LoginPage />} /><Route element={<ProtectedRoute />}><Route element={<AppLayout />}><Route path="dashboard" element={<DashboardPage />} /><Route path="students" element={<StudentsPage />} /><Route path="students/:id" element={<StudentDetailsPage />} /><Route path="programs" element={<ProgramsPage />} /><Route path="courses" element={<CoursesPage />} /><Route path="academic-terms" element={<AcademicTermsPage />} /><Route path="course-offerings" element={<CourseOfferingsPage />} /><Route path="enrollments" element={<EnrollmentsPage />} /><Route path="grades" element={<GradesPage />} /><Route path="academic-record" element={<AcademicRecordPage />} /><Route path="profile" element={<ProfilePage />} />{pages.filter(([path]) => !['dashboard','students','programs','courses','academic-terms','course-offerings','enrollments','grades','academic-record','profile'].includes(path)).map(([path, title]) => <Route key={path} path={path} element={<PlaceholderPage title={title} />} />)}<Route path="/403" element={<ForbiddenPage />} /></Route></Route><Route path="/" element={<Navigate to="/dashboard" replace />} /><Route path="*" element={<NotFoundPage />} /></Routes>
}
