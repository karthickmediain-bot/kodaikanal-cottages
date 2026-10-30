# Adding blog posts (no developer needed)

1. Go to **https://www.cottageskodaikanal.com/blog-admin/**
2. First visit only: create the first admin account (name, email, password of 10+ characters). Do this right after uploading the site. The "Team login" button on the blog page also leads here.
   - Accounts are stored in the website database (table `blog_users`, created automatically using the db details in `api/config.php`).
   - Admins add teammates under **Team** (name, email, temporary password, role). **Editors** write and edit posts; **Admins** also manage the team. Anyone can change their own password under **My account**.
3. Click **+ New post**. Fill in the title, short summary, category and date, add a cover image, then write the article in the editor (headings, bold, lists, links, images).
4. Click **Save as draft** (not public) or **Publish** (live at `/blog/<title>` immediately).
   - A future publish date keeps the post hidden until that day.
5. To change or remove a post, open it from the post list. **Delete** is permanent.

Notes
- Images: JPG/PNG/WebP under 5 MB. Compress large photos first (e.g. squoosh.app) so pages stay fast.
- Forgot a password? Another admin can use **Team → Reset password**. If the only admin is locked out, delete their row from `blog_users` in phpMyAdmin (or empty the table) and visit `/blog-admin/` to create a new first admin.
- Back up the `blog-system/data/` and `images/blog/` folders: they hold all posts and uploaded images.
- Hosting must run PHP (Hostinger does). The folders `blog-system/data/` and `images/blog/` must be writable.
