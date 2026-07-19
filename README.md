# Faculty Theme Setup Tutorial

Faculty Theme is the visual theme for the MEDAL research group website. It controls the site header, navigation, homepage, page banners, footer, gallery, contact page, research page, and general styling.

It also provides theme-level SEO/social metadata, Open Graph/Twitter card tags, lightweight Organization schema, lazy-loaded imagery, gallery keyboard controls, and a last-saved indicator in the theme settings panel.

The Academic Student Directory / Faculty Toolkit plugin controls people data: PI information, students, student profiles, private profile-edit links, database-backed records, CSV exports/backups, and the `/research-group/` route.

In short:

- Use **Faculty Theme** menu for website appearance and general pages.
- Use **Faculty Toolkit** menu for PI/student/member data.

## 1. Activate the theme and plugin

1. Go to **WordPress Admin > Appearance > Themes**.
2. Activate **Faculty Theme**.
3. Go to **Plugins > Installed Plugins**.
4. Activate **Academic Faculty Toolkit**.
5. Go to **Settings > Permalinks** and click **Save Changes** once. This refreshes routes such as `/research-group/`.

## Installable releases and updates

From this theme directory, create an installable theme ZIP with:

```powershell
.\build-release.ps1
```

This creates `releases/faculty-theme.zip`.

When working from the current XAMPP `wp-content` workspace, you can also run:

```powershell
.\themes\faculty-theme\build-release.ps1
```

The theme can be updated later through **Appearance > Themes > Add New > Upload Theme**. WordPress replaces the theme code while preserving pages, posts, menus, uploads, and Faculty Theme settings stored in the database.

Optional automatic theme updates are supported if a release JSON endpoint is configured in `wp-config.php`:

```php
define('FACULTY_THEME_UPDATE_JSON', 'https://raw.githubusercontent.com/medal-ece/Academic-Faculty-Theme/main/update-manifest.json');
```

The JSON should include at least:

```json
{
  "version": "1.4.1",
  "download_url": "https://github.com/medal-ece/Academic-Faculty-Theme/releases/download/v1.4.1/faculty-theme.zip",
  "details_url": "https://github.com/medal-ece/Academic-Faculty-Theme/releases/tag/v1.4.1"
}
```

For source control, connect this directory to:

```text
https://github.com/medal-ece/Academic-Faculty-Theme
```

## 2. Create the required pages

Go to **Pages > Add New** and create these pages.

| Page title | Recommended slug | Template / setting |
| --- | --- | --- |
| Home | `home` | Default template; assigned as Homepage under Reading settings |
| NEWS | `news` | Assigned as Posts page under Reading settings |
| Research | `research` | Template: **Faculty Research** |
| Courses | `courses` | Default page template |
| Contact | `contact` | Template: **Faculty Contact** |
| Gallery | `gallery` | Template: **Faculty Gallery** |

Do **not** create a normal WordPress page at `/research-group/`. The plugin owns that route automatically.

## 3. Configure the homepage

Go to **Settings > Reading**.

Set:

- **Your homepage displays:** `A static page`
- **Homepage:** `Home`
- **Posts page:** `NEWS`

If the homepage is set to static but the Homepage dropdown is empty, the homepage may appear missing.

## 4. Build the main navigation menu

Go to **Appearance > Menus**.

Create a menu such as `Primary Navigation`, add these links/pages, and assign it to **Primary Navigation**:

1. Home
2. NEWS
3. Research
4. Research Group
5. Courses
6. Gallery
7. Contact

For **Research Group**, use a custom link:

```text
/research-group/
```

You can also create a footer menu and assign it to **Footer Navigation**.

## 5. Configure Faculty Theme settings

Open the top-level **Faculty Theme** menu in WordPress Admin.

### General tab

Use this tab for global branding:

- Institution line, for example `The University of Utah`
- Logo
- Shared page hero image
- Shared page hero title size

The shared page hero image is used behind page titles on pages such as Courses, Research, Contact, News, and Gallery. It also visually aligns the plugin profile pages with the rest of the theme.

### Colors tab

Use this tab to adjust the site palette without editing CSS:

- primary and dark accent colors
- heading, body, muted, border, soft background, and surface colors
- navigation and footer colors
- gallery timeline/card accent colors
- vacancies/open-positions accent color

Each color has a default value and a **Return to default** button, so you can safely experiment and return to the original palette.

### Front Page tab

Use this tab to decide which homepage sections appear:

- MEDAL introduction
- Slideshow
- Homepage gadgets/widgets
- Latest news list
- Logo strip

For a clean research-group homepage, keep it short: introduction, optional slideshow, news list, and logos.

### MEDAL Intro tab

Use this tab for the main homepage introduction:

- Eyebrow text
- Main title
- Subtitle
- Description
- Button text and URL
- Introduction image

This is where the group explains who MEDAL is and what the lab does.

### Slider tab

Use this tab to add homepage slideshow images.

Each slide supports:

- image
- heading
- summary
- button text and URL
- duration
- transition style
- image fit option

Use short slide text. Long text is clipped/faded so the layout remains clean.

Slides are collapsible and draggable. Collapse finished slides before dragging when the list becomes long.

The admin panel also shows a compact slide preview so you can quickly check the image, heading, and summary without opening the public homepage.

### News tab

Use this tab to control the homepage news preview:

- section title
- post category
- number of posts
- archive/news URL

Actual news items are normal WordPress posts under **Posts > Add New**. Add a featured image to each post if you want it to appear visually on the News page.

### Contact tab

Use this tab for the Contact page:

- page description
- address
- email
- phone
- map embed code

The Contact page must use the **Faculty Contact** page template.

### Research tab

Use this tab for the Research page:

- research introduction
- research areas
- funded projects with status badges such as Active, Completed, Paused, or Pending
- sponsors

The Research page must use the **Faculty Research** page template.

Research areas, funded projects, and sponsors are collapsible and draggable in the admin panel.

### Gallery tab

Use this tab for the Gallery page:

- gallery introduction
- timeline-based gallery events
- exact event date, including day
- event title and description
- multiple image URLs per event/deck
- events per load, which controls how many albums appear before the **Load more gallery events** button

The Gallery page must use the **Faculty Gallery** page template.

Gallery events are sorted automatically by event date, newest first. Use one event/deck per activity, such as:

- `Bowling Night 2026`
- `Conference 2026`
- `Lab Tour`
- `Graduation`

Gallery events are collapsible and draggable in the admin panel. The public page still sorts events newest-first by date. The admin panel shows a small stacked-deck preview for each event. Older events are progressively revealed with the Load more button.

### Visuals / Logos tab

Use this tab for:

- scrolling/parallax background picture bands
- bottom logo strip
- university, department, group, sponsor, and partner logos

Background picture bands and logos are collapsible and draggable.

### Footer tab

Use this tab for:

- lab address / office information
- additional footer note

The footer has a left lab-info area and a right footer menu.

### Import / Export tab

Use this tab to download a JSON copy of all Faculty Theme settings or paste a previous JSON export to restore/move the theme setup. Imported JSON is sanitized with the same rules as the settings screen.

## 6. Configure the Academic Faculty Toolkit plugin

Open the top-level **Faculty Toolkit** menu.

Use this menu only for people/profile data.

### PI Information

Use this tab to enter:

- PI name
- title
- department
- institution
- office/contact information
- PI image
- short bio
- full profile biography
- education
- professional experience
- honors and awards
- research interests

The PI profile is automatically available at:

```text
/research-group/PI/
```

### Students

Use this tab to add/edit students and lab members.

Each student/member can have:

- name
- profile slug
- active/past status
- category
- email/private email
- public secondary email
- website
- bio
- pronouns
- research interests
- hobbies
- current position
- profile image
- education records

Student profile pages are automatic. Example:

```text
/research-group/student-profile-slug/
```

You do not create WordPress pages for each student.

### Profile Links

Use this tab to generate private edit links for students.

Students can update approved profile fields without having WordPress accounts. The plugin keeps primary administrative data locked.

### Email Settings

Use this tab to configure the email message sent with private profile links.

Useful placeholders:

- `{student_name}`
- `{edit_link}`
- `{site_name}`
- `{expires_at}`

### Settings

Use this tab for:

- CSV exports/backups
- student category ordering
- pronoun options
- education title options
- plugin path and shortcode information

## 7. Add news posts

Go to **Posts > Add New**.

For each news item:

1. Add a title.
2. Add the news text.
3. Set the publish date.
4. Add a featured image if available.
5. Assign a category if you want the homepage to show only selected news.
6. Publish.

The News page lists posts automatically because it is assigned as the Posts page under **Settings > Reading**.

## 8. Add courses

For now, courses can be a normal WordPress page.

Go to **Pages > Courses** and write the course content with normal WordPress blocks. If the site later needs filtering, semesters, or automatic course archives, that can become a future plugin feature.

## 9. Test the site

After setup, check these URLs:

```text
/
/news/
/research/
/research-group/
/research-group/PI/
/courses/
/gallery/
/contact/
```

Also open at least one student profile from the Research Group page.

If `/research-group/` or profile pages show 404:

1. Go to **Settings > Permalinks**.
2. Click **Save Changes**.
3. Reload the route.

## 10. What belongs where?

Use **Faculty Theme** for:

- homepage layout
- header/navigation/footer
- colors and logos
- page hero image
- contact page content
- research page content
- gallery images
- homepage slides
- homepage news preview
- footer address and links

Use **Faculty Toolkit** for:

- PI data
- student/member records
- student categories
- education records
- profile images
- profile editing links
- CSV exports
- `/research-group/` directory
- `/research-group/PI/`
- `/research-group/{student}/`

This separation keeps the theme responsible for presentation and the plugin responsible for people data and behavior.
