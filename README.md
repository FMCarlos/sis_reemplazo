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
