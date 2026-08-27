# Architectural & Design Decisions

## Code Structure

- **Directory layout:** The project uses `public/` as the web root, `src/` for application logic, and `views/` for templates. The SQLite database is kept outside `public/` so it can't be accessed directly over HTTP.
- **Front controller:** Requests go through `public/index.php`. This keeps routing and response handling in one place and is sufficient for a small/simple application.
- **Routing:** A small custom router is used instead of adding a framework or routing package. There are only a few routes, so a full routing library felt unnecessary.
- **Entry points:** Both `/` and `/urls` route display the URL shortening form and serve as entry points.
- **Code organization:** Application logic is split into namespaced classes such as `App\Database` and `App\UrlShortener`.
- **Dependency injection:** `UrlShortener` receives a `PDO` instance through its constructor. This keeps the class independent from how the database connection is created and also makes it easier to test.
- **SQLite setup:** Foreign key enforcement `(PRAGMA foreign_keys = ON)` is enabled as a baseline precaution, though the current single-table schema has no foreign key relationships to enforce. The short code column is indexed separately to keep lookups fast as the number of URLs grows.

## Short Code Generation

Short codes use a 62-character alphabet (`a-z`, `A-Z`, `0-9`) and are six characters long. That gives roughly 56 billion possible combinations, which is more than enough for this application.

Codes are generated with PHP's `random_int()` rather than `rand()` or `uniqid()`. If a generated code already exists, the application tries again, with a limit of 10 attempts to avoid an endless retry loop.

## Expiry Handling

Expiration dates come from the HTML `datetime-local` input and are parsed with `DateTimeImmutable` before being stored in SQLite as `Y-m-d H:i:s`.

Expired links return the same 404 response as links that don't exist. Apart from keeping the response simple, this means someone can't easily distinguish between a code that never existed and one that has expired.

The application explicitly sets `date_default_timezone_set('Europe/London')` rather than relying on PHP's `date.timezone` ini setting, which defaults to UTC when unset. This previously caused expiry times to be interpreted incorrectly during testing, a submitted "past" time was evaluated against UTC "now," making it appear to be in the future.

## Security

- **SQL injection:** All database queries use PDO prepared statements.
- **XSS:** Values rendered in HTML are escaped with `htmlspecialchars()`.
- **Server-side validation:** Client-side JavaScript validation is only there for a better user experience (instant feedback and avoiding unnecessary network round trips). PHP performs the actual validation so requests can't bypass it by disabling JavaScript or calling the endpoint directly.
- **Input validation:** Invalid URLs and expiration dates are rejected rather than silently converted to something else.
- **CSRF:** There is currently no CSRF protection because the application doesn't have users, sessions, or authentication. If accounts or authenticated state-changing actions were added, CSRF protection would be added as well.
- **Host header:** The generated short URL uses the request host. `HTTP_HOST` is user-controlled, The exploitability is minimal here since no sensitive data or authentication relies on it but a production deployment should use a configured or allow-listed hostname instead.

## Reliability

The application has a few basic safeguards:

- The short code generator has a maximum retry count and throws an exception if it can't find an unused code.
- PDO uses exception mode so database errors aren't silently ignored.
- Input is validated before database operations are performed.
- Expired links are checked before redirecting to the stored URL.

## What I would Improve With More Time

There are a few things I would change if this were going beyond a small take-home project to large scale or production:

- Store expiration times in UTC rather than relying on a server timezone.
- Use the Post/Redirect/Get pattern so refreshing the page doesn't resubmit the form.
- Move database setup into proper migration files.
- Add PHPUnit tests around URL validation, expiry handling, code generation, and redirects.
- Add rate limiting to prevent the shortening endpoint from being abused.
- Add basic analytics such as click counts and referrers if tracking is required.
- Consider checking whether a target URL is reachable before creating the short link, although I would probably make this optional rather than blocking link creation on an external request.

## If the Technology Choices Were Open

**Framework & Autoloading**: For a larger version of the application, I would use Composer for PSR-4 autoloading and a lightweight framework such as Slim rather than maintaining the routing and bootstrapping manually.

**In-Memory Caching**: For higher traffic, SQLite could eventually be replaced with PostgreSQL and Redis could be introduced for frequently accessed short codes. I wouldn't add either just for this version, though, as SQLite is more than sufficient for the current scope.

**Asset Pipeline**: For frontend assets, I would also use a proper build setup such as Vite instead of relying on a CDN based Tailwind setup.
