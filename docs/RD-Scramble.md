---
## How Scramble documents your API

Scramble reads your code automatically — no manual YAML needed. Here's exactly what it picks up and how to add more.
---

### What Scramble reads automatically

| Source                              | What it documents                                  |
| ----------------------------------- | -------------------------------------------------- |
| Route definitions                   | Path parameters (`{course}`, `{id}`) + HTTP method |
| Form Request `rules()`              | All request body / query params with types         |
| Type hints on controller params     | Model binding → UUID/int type                      |
| Return type + `$this->success(...)` | Response shape                                     |
| `$request->validate([...])`         | Inline validation params                           |
| `$request->integer('per_page', 15)` | Query param with default                           |

---

### What we added — `#[QueryParameter]` attributes

For params that aren't in Form Requests (search, filter, sort, pagination), we added PHP attributes directly on the controller method:

```php
use Dedoc\Scramble\Attributes\QueryParameter;

#[QueryParameter('search', description: 'Search in title and description.', type: 'string', example: 'Laravel')]
#[QueryParameter('filter[level]', description: 'Filter by level.', type: 'string', example: 'BEGINNER')]
#[QueryParameter('sort', description: 'Sort field: title, price, created_at.', type: 'string', example: 'price')]
#[QueryParameter('order', description: 'asc or desc.', type: 'string', default: 'desc')]
#[QueryParameter('per_page', description: 'Items per page (max 100).', type: 'integer', default: 15, example: 10)]
#[QueryParameter('page', description: 'Page number.', type: 'integer', default: 1)]
public function index(Request $request): JsonResponse
```

---

### How to add documentation in the future

**1. New query parameter on a list endpoint:**

```php
// Add above the method
#[QueryParameter('filter[status]', description: 'Filter by status.', type: 'string', example: 'ACTIVE')]
public function index(Request $request): JsonResponse
```

**2. Document a path parameter:**

```php
use Dedoc\Scramble\Attributes\PathParameter;

#[PathParameter('course', description: 'Course UUID', type: 'string', format: 'uuid')]
public function show(Course $course): JsonResponse
```

**3. Add endpoint summary + description (PHPDoc):**

```php
/**
 * List published courses.
 *
 * Supports pagination, search by title/description, and filtering by level or language.
 * Results are cached for 30 minutes.
 */
public function index(Request $request): JsonResponse
```

**4. Document a request body param with example (in Form Request):**

```php
public function rules(): array
{
    return [
        /**
         * The course title.
         * @example "Complete Laravel Masterclass"
         */
        'title' => ['required', 'string', 'max:255'],
    ];
}
```

**5. Group endpoints under a custom name:**

```php
use Dedoc\Scramble\Attributes\Group;

#[Group('Courses', weight: 2)]
final class CourseController extends ApiController
```

**6. Mark a param as optional query string (not body) on POST:**

```php
$request->validate([
    /** @query */
    'include_drafts' => ['boolean'],
]);
```

**7. Hide a param from docs:**

```php
$request->validate([
    /** @ignoreParam */
    'internal_token' => ['string'],
]);
```

---

### View your docs

```bash
# Open in browser
http://localhost:8000/docs/api

# Export as JSON (for Postman, Insomnia, etc.)
php artisan scramble:export --path=docs/openapi.json
```
