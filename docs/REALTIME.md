# Lami Realtime

Lami uses Laravel Reverb as its WebSocket server and Laravel broadcasting for server-side events.

## Architecture

- `MessageSent` broadcasts on `private-conversation.{conversationId}`.
- `routes/channels.php` authorizes only conversation participants.
- The web chat subscribes with Laravel Echo and Pusher-compatible WebSocket transport.
- The chat UI keeps a lightweight polling fallback so messages remain recoverable if the WebSocket server is unavailable.

## Local development

1. Install dependencies with Composer.
2. Configure `REVERB_APP_ID`, `REVERB_APP_KEY`, `REVERB_APP_SECRET`, `REVERB_HOST`, and `REVERB_PORT`.
3. Run the Laravel application.
4. Run `php artisan reverb:start` in a separate process.
5. Run the queue worker because `MessageSent` is a queued broadcast event.

For local browser testing, the default development WebSocket endpoint is `ws://127.0.0.1:8080`.

## Production

Run Reverb as a supervised long-lived process and expose it through the production WebSocket endpoint. Put TLS termination and origin restrictions at the edge, keep the Reverb app secret server-side, and run a persistent queue worker for broadcast jobs.

For horizontal scaling, enable Reverb scaling with Redis and ensure all application instances share the same Redis backend.

## Failure behavior

The browser marks the connection state as live/reconnecting and polls for missed messages every 10 seconds. The server remains authoritative; realtime delivery is an optimization over the normal message API.
