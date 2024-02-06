**freeCodeCamp** - APIs and Microservices Project
------

**Exercise Tracker REST API**

### User Stories:

1. I can create a user by posting form data username to /api/users and returned will be an object with user_id and username.
2. I can get an array of all users by getting /api/users with the same info as when creating a user.
3. I can add an exercise to any user by posting form data user_id, description, duration, and optionally date to /api/users/user_id/exercises. If no date is supplied it will use current date. Returned will be the user object with also the exercise fields added.
4. I can retrieve a full exercise log of any user by getting /api/users/user_id/exercises. Returned will be the user object with added array log and count (total exercise count).
5. I can retrieve part of the log of any user by also passing along optional parameters: `from` / `to` / `limit`. (Date format: yyyy-mm-dd, limit: number)