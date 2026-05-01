# Laravel API Kit - Sequence Diagram

## API V1 Authentication and Authorization Flow

```mermaid
sequenceDiagram
    participant Client as Client
    participant API as API Router
    participant Auth as Auth Service
    participant Passport as Passport
    participant Permission as Permission Service
    participant DB as Database
    participant Controller as Controller

    Client->>API: POST /api/v1/auth/register
    API->>Auth: Register User
    Auth->>DB: Create User Record
    DB-->>Auth: User Created
    Auth->>Passport: Generate API Token
    Passport-->>Auth: Token Generated
    Auth-->>API: Registration Success
    API-->>Client: User ID & Token

    Client->>API: GET /api/v1/courses<br/>Header: Authorization: Bearer {token}
    API->>Passport: Validate Token
    Passport->>DB: Check Token Validity
    DB-->>Passport: Token Valid
    Passport-->>API: Token Verified

    API->>Permission: Check Permission<br/>courses.view
    Permission->>DB: Get User Roles & Permissions
    DB-->>Permission: Permissions Retrieved
    Permission-->>API: Permission Granted

    API->>Controller: Route to CourseController@index
    Controller-->>API: Course List
    API-->>Client: JSON Response
```

## Student Enrollment with Payment Processing

```mermaid
sequenceDiagram
    participant Student as Student
    participant API as API Server
    participant Course as Course Service
    participant Payment as Payment Service
    participant PayPal as PayPal Gateway
    participant Invoice as Invoice Service
    participant DB as Database
    participant Email as Email Service

    Student->>API: GET /api/v1/courses/{id}
    API->>Course: Fetch Course Details
    Course->>DB: Query Course
    DB-->>Course: Course Data
    Course-->>API: Course Details
    API-->>Student: Course Information

    Student->>API: POST /api/v1/enrollments
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
        Payment->>Invoice: Generate Invoice
        Invoice->>DB: Create Invoice Record
        DB-->>Invoice: Invoice Created
        Invoice->>Email: Send Invoice
        Email-->>Student: Invoice Email
        Payment->>API: Payment Confirmed
    else Course is Free
        API->>DB: Create Enrollment
    end

    API->>DB: Create Enrollment Record
    DB-->>API: Enrollment Created
    API->>Email: Send Enrollment Confirmation
    Email-->>Student: Enrollment Confirmation
    API-->>Student: Enrollment Success
```

## Assignment Submission with AI Auto-Grading

```mermaid
sequenceDiagram
    participant Student as Student
    participant API as API Server
    participant Assignment as Assignment Service
    participant Submission as Submission Service
    participant AI as AI Service
    participant Grading as Grading Service
    participant DB as Database
    participant Teacher as Teacher
    participant Email as Email Service

    Student->>API: GET /api/v1/assignments/{id}
    API->>Assignment: Fetch Assignment
    Assignment->>DB: Query Assignment with Questions
    DB-->>Assignment: Assignment Data
    Assignment-->>API: Assignment Details
    API-->>Student: Assignment Questions

    Student->>API: POST /api/v1/assignments/{id}/start
    API->>Assignment: Start Assignment
    Assignment->>DB: Create AssignmentStart Record
    DB-->>Assignment: Start Recorded
    Assignment-->>API: Assignment Started
    API-->>Student: Start Time Recorded

    Student->>API: POST /api/v1/submissions
    API->>Submission: Validate Submission
    Submission->>DB: Check Assignment Status
    DB-->>Submission: Assignment Valid
    Submission->>DB: Check Time Limit
    DB-->>Submission: Within Time Limit
    API->>DB: Create Submission Record
    DB-->>API: Submission Created

    API->>AI: Auto-Grade Submission
    AI->>AI: Analyze Answers
    AI->>AI: Calculate Score
    AI-->>API: Grade & Feedback

    API->>Grading: Save Grade
    Grading->>DB: Update Submission with Grade
    DB-->>Grading: Grade Saved

    API->>Email: Notify Teacher
    Email-->>Teacher: New Submission Alert
    API-->>Student: Submission Received

    Teacher->>API: GET /api/v1/submissions/{id}
    API->>Submission: Fetch Submission
    Submission->>DB: Query Submission with Answers
    DB-->>Submission: Submission Data
    Submission-->>API: Submission Details
    API-->>Teacher: Submission Content

    Teacher->>API: POST /api/v1/submissions/{id}/grade
    API->>Grading: Process Grading
    Grading->>DB: Update Submission with Grade
    DB-->>Grading: Grade Saved
    API->>Email: Send Grade Notification
    Email-->>Student: Grade Received
    API-->>Teacher: Grade Saved

    Student->>API: GET /api/v1/submissions/{id}
    API->>Submission: Fetch Graded Submission
    Submission->>DB: Query Submission
    DB-->>Submission: Submission with Grade
    Submission-->>API: Graded Submission
    API-->>Student: Grade & Feedback
```

## Role-Based Access Control Flow

```mermaid
sequenceDiagram
    participant Admin as Admin
    participant API as API Server
    participant Auth as Auth Service
    participant Permission as Permission Service
    participant Role as Role Service
    participant DB as Database
    participant User as User Service

    Admin->>API: POST /api/v1/admin/roles
    API->>Auth: Verify Admin Token
    Auth->>DB: Check Token
    DB-->>Auth: Token Valid
    Auth-->>API: Admin Verified

    API->>Permission: Check Permission<br/>roles.create
    Permission->>DB: Get Admin Permissions
    DB-->>Permission: Permissions Retrieved
    Permission-->>API: Permission Granted

    API->>Role: Create Role
    Role->>DB: Create Role Record
    DB-->>Role: Role Created
    Role-->>API: Role Details
    API-->>Admin: Role Created

    Admin->>API: POST /api/v1/admin/roles/{id}/permissions
    API->>Permission: Assign Permissions to Role
    Permission->>DB: Link Permissions
    DB-->>Permission: Permissions Assigned
    Permission-->>API: Success
    API-->>Admin: Permissions Assigned

    Admin->>API: POST /api/v1/admin/users/{id}/roles
    API->>User: Assign Role to User
    User->>DB: Link User to Role
    DB-->>User: Role Assigned
    User->>DB: Update User Permissions Cache
    DB-->>User: Cache Updated
    User-->>API: Success
    API-->>Admin: Role Assigned to User

    User->>API: GET /api/v1/courses
    API->>Auth: Verify Token
    Auth->>DB: Check Token
    DB-->>Auth: Token Valid
    Auth-->>API: User Verified

    API->>Permission: Check Permission<br/>courses.view
    Permission->>DB: Get User Permissions
    DB-->>Permission: Permissions Retrieved
    Permission-->>API: Permission Granted

    API-->>User: Course List
```

## Certificate Generation and Completion Flow

```mermaid
sequenceDiagram
    participant Student as Student
    participant API as API Server
    participant Enrollment as Enrollment Service
    participant Certificate as Certificate Service
    participant PDF as PDF Generator
    participant DB as Database
    participant Email as Email Service

    Student->>API: GET /api/v1/enrollments/{id}
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

    Student->>API: GET /api/v1/certificates/{id}
    API->>Certificate: Fetch Certificate
    Certificate->>DB: Query Certificate
    DB-->>Certificate: Certificate Data
    Certificate-->>API: Certificate Details
    API-->>Student: Certificate Info

    Student->>API: GET /api/v1/certificates/{id}/download
    API->>Certificate: Download Certificate
    Certificate->>PDF: Retrieve PDF
    PDF-->>Certificate: PDF File
    Certificate-->>API: PDF Stream
    API-->>Student: PDF Download
```
