# ER Diagram

The database schema is in Third Normal Form (3NF). All non-key attributes depend solely on their tables primary key, and no transitive dependencies exist.

## Entities & Relationships

- **Users** (id PK, name, email, password_hash, role, avatar, created_at)
- **Profiles** (user_id PK/FK  Users.id, bio, interests, social_links)
  - 1:1 with Users
- **Threads** (id PK, user_id FK  Users.id, title, body, created_at, like_count)
  - 1:N from Users to Threads
- **Posts** (id PK, thread_id FK  Threads.id, user_id FK  Users.id, body, created_at)
  - 1:N from Threads to Posts
- **Thread_Likes** (user_id FK  Users.id, thread_id FK  Threads.id) PK(user_id,thread_id)
  - N:M between Users and Threads
- **Blogs** (id PK, author_id FK  Users.id, title, body, created_at)
  - 1:N from Users to Blogs
- **Comments** (id PK, blog_id FK  Blogs.id, user_id FK  Users.id, body, created_at)
  - 1:N from Blogs to Comments
- **Resources** (id PK, uploader_id FK  Users.id, title, file_path, created_at)
  - 1:N from Users to Resources
- **Events** (id PK, title, description, event_date, location)
- **RSVPs** (user_id FK  Users.id, event_id FK  Events.id) PK(user_id,event_id)
  - N:M between Users and Events
- **Messages** (id PK, sender_id FK  Users.id, receiver_id FK  Users.id, body, sent_at)
  - Self-join 1:N for messaging

## 3NF Details
1. All tables use atomic columns (no repeating groups).
2. Every non-key field depends on the primary key.
3. No transitive dependencies: e.g. Profiles only store profile-specific data.

