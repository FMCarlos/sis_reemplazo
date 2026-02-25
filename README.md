# Sistema de Solicitudes de Reemplazo

## Levantar entorno Docker
```bash
docker compose up -d --build
```

La aplicación queda disponible en:
- http://localhost:8080

## Comandos útiles Laravel (dentro del contenedor)
```bash
docker exec -it solicitudes_app php artisan migrate
```

## Compilar assets (Bootstrap 5 + Alpine + Vite)
```bash
docker exec -it solicitudes_app npm install
docker exec -it solicitudes_app npm run build
```

Para desarrollo de frontend:
```bash
docker exec -it solicitudes_app npm run dev
```

## Usuarios seed (solo desarrollo)
Después de correr `php artisan migrate:fresh --seed` en `/laravel`, se crean estos usuarios de prueba:

- **JEFE_SERVICIO**
  - Email: `jefe.servicio@example.com`
  - Password: `Password123!`
- **GESTION_PERSONAS**
  - Email: `gestion.personas@example.com`
  - Password: `Password123!`
- **RRHH**
  - Email: `rrhh@example.com`
  - Password: `Password123!`
