# Step 2: System Analysis

## 2.1 Problem statement
Job seekers often depend on posters, word of mouth or scattered social-media posts to find jobs, and employers have no organised way to collect and review applications. This wastes time, causes missed opportunities and makes recruitment hard to track.

## 2.2 Objectives
**General:** build a web-based system that connects job seekers and employers in one place.

**Specific:**
- Let employers publish job vacancies and review applicants online.
- Let job seekers search vacancies and apply from anywhere.
- Let an administrator control users and content.
- Show applicants the status of their applications.

## 2.3 Scope
In scope: registration/login, job posting, search, application, status tracking, admin moderation.
Out of scope (future work): file upload of CVs, email/SMS notifications, online interviews, payments, mobile app.

## 2.4 Users (actors)
| Actor | Description |
|---|---|
| Job Seeker | Searches jobs, applies, tracks applications |
| Employer | Posts jobs, reviews applicants, updates status |
| Admin | Manages users and jobs, views statistics |

## 2.5 Functional requirements
| ID | Requirement |
|---|---|
| FR1 | Users register as seeker or employer and log in/out |
| FR2 | Employers post, close and reopen jobs |
| FR3 | Anyone can browse and search jobs by keyword and category |
| FR4 | Seekers apply once per job with a CV link and cover letter |
| FR5 | Employers view applicants of their own jobs and change status |
| FR6 | Seekers view their applications and statuses |
| FR7 | Admin views statistics, disables users and hides jobs |

## 2.6 Non-functional requirements
- **Security:** hashed passwords, CSRF protection, role-based access, input validation
- **Usability:** simple, responsive interface that works on phones
- **Performance:** pages load in under 2 seconds for normal usage
- **Maintainability:** modular code, documented, automated tests
- **Portability:** runs on Windows with WAMP (Apache, PHP 8.1+, MySQL)

## 2.7 Feasibility
- **Technical:** PHP, Apache and MySQL (WAMP) are free and well documented.
- **Economic:** no licence cost; free hosting tiers are enough for a prototype.
- **Operational:** simple interface that needs no training.

## 2.8 Use cases (summary)
- **UC1 Apply for job:** Seeker opens a job, enters CV link and cover letter, submits. System rejects duplicates and expired jobs.
- **UC2 Post job:** Employer fills the form; system validates and publishes.
- **UC3 Review applicant:** Employer opens applicants, opens CV, sets status.
- **UC4 Moderate:** Admin disables a user or hides a job.
