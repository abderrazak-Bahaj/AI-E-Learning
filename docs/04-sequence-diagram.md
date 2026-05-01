# E-Learning API - Sequence Diagram

## Student Enrollment and Payment Flow

```mermaid
sequenceDiagram
    participant Student as Student
    participant API as API Server
    participant Auth as Auth Service
    participant Course as Course Service
    participant Payment as Payment Service
    participant PayPal as PayPal Gateway
    participant DB as Database
    participant Email as Email Service

    Student->>API: POST /api/auth/register
    API->>Auth: Register User
    Auth->>DB: Create User Record
    DB-->>Auth: User Created
    Auth->>Email: Send Verification Email
    Email-->>Student: Verification Email
    Auth-->>API: Registration Success
    API-->>Student: User ID & Token

    Student->>API: GET /api/courses
    API->>Course: Fetch Courses
    Course->>DB: Query Courses
    DB-->>Course: Course List
    Course-->>API: Courses Data
    API-->>Student: Course List

    Student->>API: GET /api/courses/{id}
    API->>Course: Fetch Course Details
    Course->>DB: Query Course with Relations
    DB-->>Course: Course Details
    Course-->>API: Course Data
    API-->>Student: Course Details

    Student->>API: POST /api/enrollments
    API->>Course: Check Course Availability
    Course->>DB: Verify Course Status
    DB-->>Course: Course Available

    alt Course is Paid
        API->>Payment: Initiate Payment
        Payment->>PayPal: Create Payment Order
        PayPal-->>Payment: Payment Order Created
        Payment-->>API: Payment URL
        API-->>Student: Redirect to PayPal

        Student->>PayPal: Complete Payment
        PayPal->>Payment: Payment Webhook
        Payment->>DB: Update Payment Status
        DB-->>Payment: Payment Recorded
        Payment->>API: Payment Confirmed
    else Course is Free
        API->>DB: Create Enrollment
    end

    API->>DB: Create Enrollment Record
    DB-->>API: Enrollment Created
    API->>Email: Send Enrollment Confirmation
    Email-->>Student: Enrollment Confirmation
    API-->>Student: Enrollment Success

    Student->>API: GET /api/enrollments/{id}/lessons
    API->>Course: Fetch Lessons
    Course->>DB: Query Lessons
    DB-->>Course: Lesson List
    Course-->>API: Lessons Data
    API-->>Student: Lesson List

    Student->>API: GET /api/lessons/{id}
    API->>Course: Fetch Lesson Content
    Course->>DB: Query Lesson with Resources
    DB-->>Course: Lesson Content
    Course-->>API: Lesson Data
    API-->>Student: Lesson Content

    Student->>API: POST /api/lessons/{id}/complete
    API->>Course: Mark Lesson Complete
    Course->>DB: Update Lesson Progress
    DB-->>Course: Progress Updated
    Course-->>API: Success
    API-->>Student: Lesson Marked Complete
```

## Assignment Submission and Grading Flow

```mermaid
sequenceDiagram
    participant Student as Student
    participant API as API Server
    participant Assignment as Assignment Service
    participant Grading as Grading Service
    participant DB as Database
    participant Teacher as Teacher
    participant Email as Email Service

    Student->>API: GET /api/assignments/{id}
    API->>Assignment: Fetch Assignment
    Assignment->>DB: Query Assignment with Questions
    DB-->>Assignment: Assignment Data
    Assignment-->>API: Assignment Details
    API-->>Student: Assignment Questions

    Student->>API: POST /api/submissions
    API->>Assignment: Validate Submission
    Assignment->>DB: Check Assignment Status
    DB-->>Assignment: Assignment Valid
    API->>DB: Create Submission Record
    DB-->>API: Submission Created
    API->>Email: Notify Teacher
    Email-->>Teacher: New Submission Alert
    API-->>Student: Submission Received

    Teacher->>API: GET /api/submissions/{id}
    API->>Grading: Fetch Submission
    Grading->>DB: Query Submission with Answers
    DB-->>Grading: Submission Data
    Grading-->>API: Submission Details
    API-->>Teacher: Submission Content

    Teacher->>API: POST /api/submissions/{id}/grade
    API->>Grading: Process Grading
    Grading->>DB: Calculate Score
    DB-->>Grading: Score Calculated
    Grading->>DB: Update Submission with Grade
    DB-->>Grading: Grade Saved
    API->>Email: Send Grade Notification
    Email-->>Student: Grade Received
    API-->>Teacher: Grade Saved

    Student->>API: GET /api/submissions/{id}
    API->>Grading: Fetch Graded Submission
    Grading->>DB: Query Submission
    DB-->>Grading: Submission with Grade
    Grading-->>API: Graded Submission
    API-->>Student: Grade & Feedback
```

## Certificate Generation and Course Completion Flow

```mermaid
sequenceDiagram
    participant Student as Student
    participant API as API Server
    participant Enrollment as Enrollment Service
    participant Certificate as Certificate Service
    participant PDF as PDF Generator
    participant DB as Database
    participant Email as Email Service

    Student->>API: GET /api/enrollments/{id}
    API->>Enrollment: Fetch Enrollment
    Enrollment->>DB: Query Enrollment Progress
    DB-->>Enrollment: Enrollment Data
    Enrollment-->>API: Enrollment Details
    API-->>Student: Progress Data

    Enrollment->>Enrollment: Check Completion Criteria
    Enrollment->>DB: Verify All Lessons Complete
    DB-->>Enrollment: Lessons Verified
    Enrollment->>DB: Verify Passing Score
    DB-->>Enrollment: Score Verified

    alt All Criteria Met
        Enrollment->>Certificate: Generate Certificate
        Certificate->>DB: Create Certificate Record
        DB-->>Certificate: Certificate Created
        Certificate->>PDF: Generate PDF
        PDF-->>Certificate: PDF Generated
        Certificate->>DB: Store PDF URL
        DB-->>Certificate: URL Saved
        Certificate->>Email: Send Certificate
        Email-->>Student: Certificate Email
        Certificate->>Enrollment: Certificate Ready
        Enrollment->>DB: Update Enrollment Status
        DB-->>Enrollment: Status Updated
        Enrollment-->>API: Completion Success
        API-->>Student: Course Completed!
    else Criteria Not Met
        Enrollment-->>API: Incomplete
        API-->>Student: Course In Progress
    end

    Student->>API: GET /api/certificates/{id}
    API->>Certificate: Fetch Certificate
    Certificate->>DB: Query Certificate
    DB-->>Certificate: Certificate Data
    Certificate-->>API: Certificate Details
    API-->>Student: Certificate Info

    Student->>API: GET /api/certificates/{id}/download
    API->>Certificate: Download Certificate
    Certificate->>PDF: Retrieve PDF
    PDF-->>Certificate: PDF File
    Certificate-->>API: PDF Stream
    API-->>Student: PDF Download
```

## Admin User Management Flow

```mermaid
sequenceDiagram
    participant Admin as Admin
    participant API as API Server
    participant User as User Service
    participant Auth as Auth Service
    participant DB as Database
    participant Email as Email Service

    Admin->>API: GET /api/admin/users
    API->>User: Fetch All Users
    User->>DB: Query Users
    DB-->>User: User List
    User-->>API: Users Data
    API-->>Admin: User List

    Admin->>API: GET /api/admin/users/{id}
    API->>User: Fetch User Details
    User->>DB: Query User
    DB-->>User: User Data
    User-->>API: User Details
    API-->>Admin: User Information

    Admin->>API: POST /api/admin/users/{id}/suspend
    API->>User: Suspend User
    User->>DB: Update User Status
    DB-->>User: Status Updated
    User->>Auth: Revoke Active Tokens
    Auth->>DB: Invalidate Tokens
    DB-->>Auth: Tokens Revoked
    User->>Email: Send Suspension Notice
    Email-->>Admin: Confirmation
    User-->>API: User Suspended
    API-->>Admin: Suspension Success

    Admin->>API: POST /api/admin/users/{id}/activate
    API->>User: Activate User
    User->>DB: Update User Status
    DB-->>User: Status Updated
    User->>Email: Send Activation Notice
    Email-->>Admin: Confirmation
    User-->>API: User Activated
    API-->>Admin: Activation Success

    Admin->>API: DELETE /api/admin/users/{id}
    API->>User: Delete User
    User->>DB: Soft Delete User
    DB-->>User: User Deleted
    User->>Email: Send Deletion Notice
    Email-->>Admin: Confirmation
    User-->>API: User Deleted
    API-->>Admin: Deletion Success
```
