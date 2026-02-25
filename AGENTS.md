# Sistema Solicitudes Hospital — AGENTS

## Context
- Laravel 11
- Blade + Bootstrap 5 + Alpine.js (NO SPA frameworks)
- Docker mandatory (nginx + php-fpm + mysql)
- All workflow actions are AJAX and must return JSON.

## Repo structure
- The Laravel app lives in /laravel
- Docker and docs live at repo root

## Rules
- Do not introduce Vue/React/Inertia.
- Keep UI as admin dashboard (sidebar/topbar, grey bg, white cards).
- Use Policies for authorization and enums for statuses.
- Every workflow action must write to request_actions (audit log).

## Definition of Done
- Feature includes migrations, routes, controller/service, policy updates, Blade UI, and a basic test if feasible.