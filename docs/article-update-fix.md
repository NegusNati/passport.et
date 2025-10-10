# Article Update API - PUT Request Fix

## Problem

PUT requests with `multipart/form-data` (used for file uploads) don't work properly in PHP/Laravel. This is a known limitation where PHP only parses multipart data for POST requests, not PUT/PATCH.

When sending files with a PUT request, the request body is buffered but never parsed into `$_POST` or `$_FILES`, causing the update to appear successful (200 OK) but no data is actually updated.

## Solution

The API now supports **two methods** for updating articles:

### Method 1: POST with Method Spoofing (Recommended for File Uploads)

When uploading files (featured_image, og_image), use POST with a `_method` field:

```javascript
const formData = new FormData();
formData.append('_method', 'PUT');  // ← Add this for method spoofing
formData.append('title', 'Updated Title');
formData.append('content', '<p>Updated content...</p>');
formData.append('featured_image', fileBlob);  // File upload

fetch('http://app.localhost/api/v1/admin/articles/article-slug', {
  method: 'POST',  // ← Use POST
  headers: {
    'Authorization': `Bearer ${token}`,
    // Don't set Content-Type, let browser set it with boundary
  },
  body: formData
});
```

### Method 2: PUT with JSON (For Text-Only Updates)

For updates without file uploads, use PUT with JSON:

```javascript
fetch('http://app.localhost/api/v1/admin/articles/article-slug', {
  method: 'PUT',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    title: 'Updated Title',
    excerpt: 'Updated excerpt...',
    content: '<p>Updated content...</p>',
    status: 'published',
    meta_title: 'SEO Title',
    meta_description: 'SEO Description',
  })
});
```

## Updated Fields

All text fields now support much longer content (TEXT type instead of VARCHAR):

- `title`: Unlimited length (was 255 chars)
- `meta_title`: Unlimited length (was 255 chars)  
- `excerpt`: TEXT (up to ~65k chars)
- `meta_description`: TEXT (up to ~65k chars)

## Backend Changes Made

1. **Route Update**: Changed from `Route::put()` to `Route::match(['put', 'post'])` to accept both methods
2. **Validation**: Removed `max:255` constraints from title and meta_title fields
3. **Controller**: Removed `isDirty()` check to ensure saves always occur
4. **Migration**: Added migration to convert VARCHAR(255) to TEXT for title/meta_title

## Testing

Test with curl:

```bash
# POST with method spoofing (works with files)
curl -X POST 'http://app.localhost/api/v1/admin/articles/article-slug' \
  -H 'Authorization: Bearer YOUR_TOKEN' \
  -F '_method=PUT' \
  -F 'title=Test Updated Title' \
  -F 'content=<p>Updated content</p>' \
  -F 'featured_image=@/path/to/image.jpg'

# PUT with JSON (no files)
curl -X PUT 'http://app.localhost/api/v1/admin/articles/article-slug' \
  -H 'Authorization: Bearer YOUR_TOKEN' \
  -H 'Content-Type: application/json' \
  -d '{"title":"Test Updated Title","content":"<p>Updated</p>"}'
```

## Why This Happens

- PHP's `$_POST` and `$_FILES` are only populated for POST requests
- PUT/PATCH requests with multipart/form-data go to `php://input` stream
- Laravel doesn't automatically parse multipart streams for non-POST methods
- Laravel's built-in method spoofing (`_method` field) solves this by using POST

## References

- [Laravel Method Spoofing](https://laravel.com/docs/10.x/routing#form-method-spoofing)
- [PHP RFC: Request](https://wiki.php.net/rfc/request_parsing)
