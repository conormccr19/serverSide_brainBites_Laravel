# BrainBites Website Status (So Far)

## 1. Project Snapshot
BrainBites is a visual-first Q&A platform built with Laravel, focused on curiosity-driven learning.

The app currently supports:
1. Community posts with rich visuals and categories
2. Social actions (likes, bookmarks, follows)
3. Nested comments with improved UX
4. AI assistant workflows (brainBot)
5. Public user profiles
6. PWA basics (manifest + service worker + offline page)

## 2. Current Tech Stack
1. Laravel 12+
2. PHP 8.2+
3. MySQL
4. Vite asset pipeline
5. Alpine.js + custom vanilla JavaScript
6. OpenRouter API integration for brainBot

## 3. Core User Features
### 3.1 Posts
1. Create, edit, delete posts (policy-based authorization)
2. Public and draft visibility support
3. Scheduled publishing support via published_at
4. Category filtering, searching, and sorting
5. Reading-time and difficulty indicators
6. Related-post recommendations on post detail pages

### 3.2 Engagement
1. Like/unlike posts (non-admin users)
2. Bookmark/unsave posts (non-admin users)
3. Follow/unfollow creators
4. Dedicated Following feed page

### 3.3 Comments (Upgraded)
1. Nested comments and replies
2. Upvote helpful comments
3. Sort comments by Top and New
4. Collapsible long reply threads with show more/show fewer
5. AJAX comment posting and upvoting (no full-page refresh)

### 3.4 Public Profiles
1. Public profile route: /u/{username}
2. Profile includes:
   1. Avatar, display name, username, bio
   2. Follower and following counts
   3. Public post and likes stats
   4. Top posts (most liked)
   5. Recent posts
3. Follow/unfollow available from public profile when authenticated
4. Author names across post views link to public profiles

### 3.5 brainBot
1. Dedicated chat page
2. Post-level contextual question prompts
3. Inline paragraph simplification tools
4. Revision and flashcard-related helper workflows

## 4. UI/UX Improvements Already Added
1. Themed global footer aligned with site style
2. Improved navbar ordering and active-page highlighting
3. Reusable back-navigation button across pages
4. Comments moved below post content (full-width discussion flow)
5. Stronger action button styling for inline paragraph tools
6. Interactive table of contents:
   1. Active section tracking
   2. Smooth scroll navigation
   3. Progress indicator

## 5. PWA Support (Baseline)
1. Web app manifest added
2. Service worker registration added
3. Offline fallback page added
4. Localhost service worker behavior adjusted for dev cache stability

## 6. Access and Navigation
### 6.1 Main Routes
1. / -> Home/Explore feed
2. /posts -> Explore posts
3. /posts/{post} -> Post detail
4. /brainbot -> brainBot page
5. /glossary -> Glossary page
6. /following -> Following feed (auth)
7. /bookmarks -> Bookmarks (auth)
8. /dashboard -> Dashboard (auth)
9. /profile -> Profile settings (auth, non-admin)
10. /u/{username} -> Public creator profile

### 6.2 Admin-Oriented
1. /admin/contact-messages -> Admin inbox

## 7. Data Model Highlights
1. users table includes role, google_id, profile_photo_path, username, bio
2. posts includes content, image fields, visibility/publish state, slug
3. follows for follower-followed user relationships
4. comments supports nested threads via parent_comment_id
5. comment_votes supports helpful upvotes on comments
6. likes and bookmarks support post interactions

## 8. Operational Status
1. Database migrations include new social/profile/comment-vote schema
2. Frontend builds successfully with Vite
3. Core UX flows implemented and connected in UI

## 9. Recommended Next Steps
1. Notifications center (replies, upvotes, followed-user activity)
2. Creator badges/expertise tags for profile discovery
3. Saved bookmark collections (folders)
4. Real-time updates for comments and votes via broadcasting

## 10. Quick Summary
BrainBites has progressed from a post feed into a social, profile-driven learning platform with improved commenting UX, creator discovery, and foundational PWA support.
