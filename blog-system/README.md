# Adding blog posts (no developer needed)

1. Go to **https://www.cottageskodaikanal.com/blog-admin/**
2. Log in with your team account. There is no public sign-up: **only an admin can create accounts** (under **Team**: name, email, temporary password, role). **Editors** write and edit posts; **Admins** also manage the team. Anyone can change their own password under **My account**.
   - **Forgot password?** Use the link on the login page. A reset link is emailed to the account's address and works for one hour.
   - **First admin (site owner, once):** add `'blog_admin' => ['name' => 'Your Name', 'email' => 'you@example.com', 'password' => '10+ characters']` to `api/config.php`; the account is created on the first visit to `/blog-admin/`. Remove the password from the file afterwards. Or run `php blog-system/create-admin.php "Your Name" you@example.com "password"` over SSH.
   - Accounts live in the website database (table `blog_users`, created automatically from the db details in `api/config.php`). Reset emails use the `smtp` details in the same file.
3. Click **+ New post**. Fill in the title, short summary, category and date, add a cover image, then write the article in the editor (headings, bold, lists, links, images).
4. Click **Save as draft** (not public) or **Publish** (live at `/blog/<title>` immediately).
   - A future publish date keeps the post hidden until that day.
5. To change or remove a post, open it from the post list. **Delete** is permanent.

Notes
- Images: JPG/PNG/WebP under 5 MB. Compress large photos first (e.g. squoosh.app) so pages stay fast.
- Locked out and the reset email does not arrive? Another admin can use **Team → Reset password**, or the site owner can run `create-admin.php` (above) with the same email to set a new password.
- Back up the `blog-system/data/` and `images/blog/` folders: they hold all posts and uploaded images.
- Hosting must run PHP (Hostinger does). The folders `blog-system/data/` and `images/blog/` must be writable.
