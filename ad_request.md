# Advertisement Request API - Frontend Integration Guide

Base URL: `http://your-domain.com/api/v1`

## Table of Contents
1. [Client Form API](#client-form-api)
2. [Admin Dashboard API](#admin-dashboard-api)
3. [Response Types](#response-types)
4. [Error Handling](#error-handling)

---

## Client Form API

### Submit Advertisement Request
**No authentication required** - Anyone can submit a request.

```
POST /advertisement-requests
```

#### Request Body (multipart/form-data or JSON)

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `phone_number` | string | Yes | Contact phone number (max 20 chars) |
| `email` | string | No | Email address |
| `full_name` | string | Yes | Full name (max 255 chars) |
| `company_name` | string | No | Company/Business name (max 255 chars) |
| `description` | string | Yes | Advertisement request details (10-5000 chars) |
| `file` | file | No | Attachment (PDF, DOC, DOCX, JPG, PNG - max 10MB) |

#### Example Request (Axios)

```javascript
// With file upload
const formData = new FormData();
formData.append('phone_number', '+251912345678');
formData.append('email', 'business@example.com');
formData.append('full_name', 'John Doe');
formData.append('company_name', 'Tech Corp');
formData.append('description', 'We would like to advertise our new product...');
formData.append('file', fileInput.files[0]); // optional

axios.post('/api/v1/advertisement-requests', formData, {
  headers: {
    'Content-Type': 'multipart/form-data'
  }
})
.then(response => {
  console.log('Request submitted:', response.data);
  // Show success message to user
})
.catch(error => {
  console.error('Error:', error.response.data);
  // Show validation errors
});
```

```javascript
// Without file (JSON)
axios.post('/api/v1/advertisement-requests', {
  phone_number: '+251912345678',
  email: 'business@example.com',
  full_name: 'John Doe',
  company_name: 'Tech Corp',
  description: 'We would like to advertise our new product...'
}, {
  headers: {
    'Content-Type': 'application/json'
  }
})
.then(response => {
  console.log('Success:', response.data);
})
.catch(error => {
  console.error('Error:', error.response.data);
});
```

#### Success Response (201 Created)

```json
{
  "data": {
    "id": 1,
    "phone_number": "+251912345678",
    "email": "business@example.com",
    "full_name": "John Doe",
    "company_name": "Tech Corp",
    "description": "We would like to advertise our new product...",
    "file_url": "http://domain.com/storage/advertisements/files/abc123.pdf",
    "status": "pending",
    "contacted_at": null,
    "created_at": "2025-10-08T08:43:29+00:00",
    "updated_at": "2025-10-08T08:43:29+00:00"
  }
}
```

---

## Admin Dashboard API

**All admin endpoints require authentication.**

### Authentication

Include the Bearer token in all admin requests:

```javascript
const config = {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  }
};
```

---

### 1. List All Requests

```
GET /admin/advertisement-requests
```

#### Query Parameters (All Optional)

| Parameter | Type | Description |
|-----------|------|-------------|
| `status` | string | Filter by status: `pending`, `contacted`, `approved`, `rejected` |
| `full_name` | string | Search by name (partial match) |
| `company_name` | string | Search by company (partial match) |
| `phone_number` | string | Search by phone (prefix match) |
| `created_after` | date | Filter by date (YYYY-MM-DD) |
| `created_before` | date | Filter by date (YYYY-MM-DD) |
| `sort` | string | Sort column: `created_at`, `status`, `full_name` |
| `sort_dir` | string | Sort direction: `asc`, `desc` (default: `desc`) |
| `page` | integer | Page number (default: 1) |
| `per_page` | integer | Items per page (default: 20, max: 100) |

#### Example Request

```javascript
// Get pending requests, sorted by newest
axios.get('/api/v1/admin/advertisement-requests', {
  params: {
    status: 'pending',
    sort: 'created_at',
    sort_dir: 'desc',
    per_page: 10,
    page: 1
  },
  headers: {
    'Authorization': `Bearer ${token}`
  }
})
.then(response => {
  const { data, meta, links } = response.data;
  console.log('Requests:', data);
  console.log('Total:', meta.total);
  console.log('Current page:', meta.current_page);
})
.catch(error => {
  console.error('Error:', error.response.data);
});
```

#### Success Response (200 OK)

```json
{
  "data": [
    {
      "id": 1,
      "phone_number": "+251912345678",
      "email": "business@example.com",
      "full_name": "John Doe",
      "company_name": "Tech Corp",
      "description": "We would like to advertise...",
      "file_url": "http://domain.com/storage/advertisements/files/abc.pdf",
      "status": "pending",
      "contacted_at": null,
      "created_at": "2025-10-08T08:43:29+00:00",
      "updated_at": "2025-10-08T08:43:29+00:00",
      "admin_notes": null
    }
  ],
  "links": {
    "first": "http://domain.com/api/v1/admin/advertisement-requests?page=1",
    "last": "http://domain.com/api/v1/admin/advertisement-requests?page=5",
    "prev": null,
    "next": "http://domain.com/api/v1/admin/advertisement-requests?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "to": 10,
    "per_page": 10,
    "total": 45,
    "last_page": 5,
    "has_more": true
  },
  "filters": {
    "status": "pending"
  }
}
```

---

### 2. View Single Request

```
GET /admin/advertisement-requests/{id}
```

#### Example Request

```javascript
axios.get(`/api/v1/admin/advertisement-requests/${requestId}`, {
  headers: {
    'Authorization': `Bearer ${token}`
  }
})
.then(response => {
  console.log('Request details:', response.data.data);
})
.catch(error => {
  if (error.response.status === 404) {
    console.error('Request not found');
  }
});
```

#### Success Response (200 OK)

```json
{
  "data": {
    "id": 1,
    "phone_number": "+251912345678",
    "email": "business@example.com",
    "full_name": "John Doe",
    "company_name": "Tech Corp",
    "description": "We would like to advertise our new product...",
    "file_url": "http://domain.com/storage/advertisements/files/abc123.pdf",
    "status": "contacted",
    "contacted_at": "2025-10-08T00:00:00+00:00",
    "created_at": "2025-10-08T08:43:29+00:00",
    "updated_at": "2025-10-08T10:15:00+00:00",
    "admin_notes": "Called business owner. Schedule meeting for next week."
  }
}
```

---

### 3. Update Request (Change Status/Add Notes)

```
PATCH /admin/advertisement-requests/{id}
```

#### Request Body (All fields optional)

| Field | Type | Description |
|-------|------|-------------|
| `status` | string | One of: `pending`, `contacted`, `approved`, `rejected` |
| `admin_notes` | string | Internal notes (max 5000 chars) |
| `contacted_at` | date | Date contacted (YYYY-MM-DD or ISO8601) |

#### Example Request

```javascript
// Mark as contacted
axios.patch(`/api/v1/admin/advertisement-requests/${requestId}`, {
  status: 'contacted',
  admin_notes: 'Called business owner. They are interested in banner ads.',
  contacted_at: '2025-10-08'
}, {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  }
})
.then(response => {
  console.log('Updated:', response.data.data);
  // Show success message
})
.catch(error => {
  console.error('Error:', error.response.data);
});
```

#### Success Response (200 OK)

```json
{
  "data": {
    "id": 1,
    "phone_number": "+251912345678",
    "email": "business@example.com",
    "full_name": "John Doe",
    "company_name": "Tech Corp",
    "description": "We would like to advertise...",
    "file_url": "http://domain.com/storage/advertisements/files/abc.pdf",
    "status": "contacted",
    "contacted_at": "2025-10-08T00:00:00+00:00",
    "created_at": "2025-10-08T08:43:29+00:00",
    "updated_at": "2025-10-08T10:20:00+00:00",
    "admin_notes": "Called business owner. They are interested in banner ads."
  }
}
```

---

### 4. Delete Request

```
DELETE /admin/advertisement-requests/{id}
```

#### Example Request

```javascript
axios.delete(`/api/v1/admin/advertisement-requests/${requestId}`, {
  headers: {
    'Authorization': `Bearer ${token}`
  }
})
.then(response => {
  console.log('Deleted successfully');
  // Remove from list
})
.catch(error => {
  console.error('Error:', error.response.data);
});
```

#### Success Response (204 No Content)

No response body. Request successfully deleted (soft delete).

---

## Response Types

### Status Values

| Status | Description |
|--------|-------------|
| `pending` | New request, not yet contacted |
| `contacted` | Admin has reached out to the business |
| `approved` | Request approved, proceeding with ad placement |
| `rejected` | Request declined |

### Date Formats

All timestamps are returned in ISO 8601 format with timezone:
```
2025-10-08T08:43:29+00:00
```

Convert to local time in your frontend:
```javascript
new Date('2025-10-08T08:43:29+00:00').toLocaleString()
```

---

## Error Handling

### Validation Errors (400 Bad Request)

```json
{
  "message": "The phone number field is required. (and 1 more error)",
  "errors": {
    "phone_number": [
      "The phone number field is required."
    ],
    "description": [
      "The description field must be at least 10 characters."
    ]
  }
}
```

#### Handle in Frontend

```javascript
.catch(error => {
  if (error.response.status === 422 || error.response.status === 400) {
    const errors = error.response.data.errors;
    // Display validation errors
    Object.keys(errors).forEach(field => {
      console.log(`${field}: ${errors[field].join(', ')}`);
    });
  }
});
```

---

### Authentication Error (401 Unauthorized)

```json
{
  "message": "Unauthenticated."
}
```

**Action**: Redirect user to login page.

---

### Authorization Error (403 Forbidden)

```json
{
  "message": "This action is unauthorized."
}
```

**Action**: User doesn't have `manage-advertisements` permission.

---

### Not Found (404 Not Found)

```json
{
  "message": "No query results for model [AdvertisementRequest] 123"
}
```

**Action**: Display "Request not found" message.

---

### Rate Limit (429 Too Many Requests)

```json
{
  "status": "error",
  "code": "rate_limit_exceeded",
  "message": "Too many requests. Please slow down and try again shortly."
}
```

**Rate Limits**:
- Anonymous: 60 requests/minute
- Authenticated: 120 requests/minute
- Premium: 240 requests/minute

---

## Complete React/Axios Example

### Client Form Component

```javascript
import { useState } from 'react';
import axios from 'axios';

function AdvertisementRequestForm() {
  const [formData, setFormData] = useState({
    phone_number: '',
    email: '',
    full_name: '',
    company_name: '',
    description: '',
    file: null
  });
  const [loading, setLoading] = useState(false);
  const [errors, setErrors] = useState({});
  const [success, setSuccess] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setErrors({});
    setSuccess(false);

    const data = new FormData();
    Object.keys(formData).forEach(key => {
      if (formData[key]) {
        data.append(key, formData[key]);
      }
    });

    try {
      const response = await axios.post('/api/v1/advertisement-requests', data, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      
      setSuccess(true);
      setFormData({
        phone_number: '',
        email: '',
        full_name: '',
        company_name: '',
        description: '',
        file: null
      });
      console.log('Submitted:', response.data);
    } catch (error) {
      if (error.response?.data?.errors) {
        setErrors(error.response.data.errors);
      } else {
        alert('An error occurred. Please try again.');
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <form onSubmit={handleSubmit}>
      {success && <div className="success">Request submitted successfully!</div>}
      
      <input
        type="text"
        placeholder="Full Name *"
        value={formData.full_name}
        onChange={(e) => setFormData({...formData, full_name: e.target.value})}
      />
      {errors.full_name && <span className="error">{errors.full_name[0]}</span>}

      <input
        type="tel"
        placeholder="Phone Number *"
        value={formData.phone_number}
        onChange={(e) => setFormData({...formData, phone_number: e.target.value})}
      />
      {errors.phone_number && <span className="error">{errors.phone_number[0]}</span>}

      <input
        type="email"
        placeholder="Email (optional)"
        value={formData.email}
        onChange={(e) => setFormData({...formData, email: e.target.value})}
      />

      <input
        type="text"
        placeholder="Company Name (optional)"
        value={formData.company_name}
        onChange={(e) => setFormData({...formData, company_name: e.target.value})}
      />

      <textarea
        placeholder="Describe your advertisement needs *"
        value={formData.description}
        onChange={(e) => setFormData({...formData, description: e.target.value})}
        rows={5}
      />
      {errors.description && <span className="error">{errors.description[0]}</span>}

      <input
        type="file"
        accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
        onChange={(e) => setFormData({...formData, file: e.target.files[0]})}
      />
      {errors.file && <span className="error">{errors.file[0]}</span>}

      <button type="submit" disabled={loading}>
        {loading ? 'Submitting...' : 'Submit Request'}
      </button>
    </form>
  );
}
```

---

### Admin Dashboard Component

```javascript
import { useState, useEffect } from 'react';
import axios from 'axios';

function AdminDashboard({ token }) {
  const [requests, setRequests] = useState([]);
  const [loading, setLoading] = useState(true);
  const [filters, setFilters] = useState({
    status: '',
    page: 1,
    per_page: 20
  });
  const [meta, setMeta] = useState(null);

  const fetchRequests = async () => {
    setLoading(true);
    try {
      const response = await axios.get('/api/v1/admin/advertisement-requests', {
        params: filters,
        headers: { 'Authorization': `Bearer ${token}` }
      });
      
      setRequests(response.data.data);
      setMeta(response.data.meta);
    } catch (error) {
      console.error('Error fetching requests:', error);
    } finally {
      setLoading(false);
    }
  };

  const updateStatus = async (id, status) => {
    try {
      await axios.patch(`/api/v1/admin/advertisement-requests/${id}`, 
        { status },
        { headers: { 'Authorization': `Bearer ${token}` } }
      );
      
      // Refresh list
      fetchRequests();
    } catch (error) {
      console.error('Error updating status:', error);
    }
  };

  const deleteRequest = async (id) => {
    if (!confirm('Delete this request?')) return;
    
    try {
      await axios.delete(`/api/v1/admin/advertisement-requests/${id}`, {
        headers: { 'Authorization': `Bearer ${token}` }
      });
      
      // Refresh list
      fetchRequests();
    } catch (error) {
      console.error('Error deleting request:', error);
    }
  };

  useEffect(() => {
    fetchRequests();
  }, [filters]);

  return (
    <div>
      <h1>Advertisement Requests</h1>
      
      <select 
        value={filters.status} 
        onChange={(e) => setFilters({...filters, status: e.target.value, page: 1})}
      >
        <option value="">All Statuses</option>
        <option value="pending">Pending</option>
        <option value="contacted">Contacted</option>
        <option value="approved">Approved</option>
        <option value="rejected">Rejected</option>
      </select>

      {loading ? (
        <p>Loading...</p>
      ) : (
        <>
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Company</th>
                <th>Phone</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {requests.map(req => (
                <tr key={req.id}>
                  <td>{req.id}</td>
                  <td>{req.full_name}</td>
                  <td>{req.company_name || '-'}</td>
                  <td>{req.phone_number}</td>
                  <td>
                    <span className={`badge badge-${req.status}`}>
                      {req.status}
                    </span>
                  </td>
                  <td>{new Date(req.created_at).toLocaleDateString()}</td>
                  <td>
                    <select 
                      value={req.status}
                      onChange={(e) => updateStatus(req.id, e.target.value)}
                    >
                      <option value="pending">Pending</option>
                      <option value="contacted">Contacted</option>
                      <option value="approved">Approved</option>
                      <option value="rejected">Rejected</option>
                    </select>
                    <button onClick={() => deleteRequest(req.id)}>Delete</button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>

          {meta && (
            <div className="pagination">
              <button 
                disabled={!meta.has_more}
                onClick={() => setFilters({...filters, page: filters.page + 1})}
              >
                Next Page
              </button>
              <span>Page {meta.current_page} of {meta.last_page}</span>
            </div>
          )}
        </>
      )}
    </div>
  );
}
```

---

## Quick Reference

### Base URLs
```
Production: https://passport.et/api/v1
Development: http://localhost:8082/api/v1
```

### Endpoints Summary

| Method | Endpoint | Auth | Purpose |
|--------|----------|------|---------|
| POST | `/advertisement-requests` | No | Submit request |
| GET | `/admin/advertisement-requests` | Yes | List all |
| GET | `/admin/advertisement-requests/{id}` | Yes | View one |
| PATCH | `/admin/advertisement-requests/{id}` | Yes | Update |
| DELETE | `/admin/advertisement-requests/{id}` | Yes | Delete |

### Key Fields

**Required on submit**: `phone_number`, `full_name`, `description`  
**Admin only**: `admin_notes`, `status`, `contacted_at`  
**File types**: PDF, DOC, DOCX, JPG, JPEG, PNG (max 10MB)

---

**Need help?** Contact the backend team or check the full API documentation.
