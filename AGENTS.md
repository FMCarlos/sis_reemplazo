# AGENTS.md

## Contexto del Proyecto

Este proyecto originalmente fue desarrollado como un sistema de solicitudes de reemplazo con un flujo complejo de visación interna (Jefe de Servicio → Gestión de Personas → RRHH).

Actualmente, el objetivo ha cambiado.

El sistema debe evolucionar hacia una **plataforma institucional de formularios**, enfocada en:

- captura de datos
- generación de PDF
- almacenamiento de solicitudes
- trazabilidad simple
- escalabilidad para múltiples tipos de formularios

NO se debe continuar desarrollando el flujo complejo anterior.

---

## Objetivo del Sistema

Convertir el sistema en una **plataforma modular de formularios institucionales**, donde:

- un usuario completa un formulario
- el sistema valida los datos
- se genera un PDF
- se guarda el registro
- se puede consultar posteriormente

---

## Principios de Desarrollo

- Simplicidad por sobre complejidad
- Evitar workflows innecesarios
- Diseño modular
- Código reutilizable
- Separación de responsabilidades
- Escalabilidad futura

---

## Arquitectura Objetivo

El sistema debe basarse en:

- form_types → tipos de formularios
- form_submissions → instancias enviadas
- payload_json → datos dinámicos
- PDF por tipo de formulario
- eventos simples de trazabilidad

---

## Lo que NO se debe hacer

- No extender Request como modelo principal
- No seguir agregando lógica a RequestWorkflowService
- No implementar flujos internos complejos
- No hardcodear formularios en controladores

---

## Lo que SÍ se debe hacer

- Crear una estructura modular por tipo de formulario
- Separar generación de PDF en servicios
- Usar payload JSON para datos flexibles
- Mantener lógica desacoplada
- Preparar el sistema para múltiples formularios

---

## Módulos esperados a futuro

- Solicitud de reemplazo
- Horas extraordinarias
- Notificación de eventos
- Tecnovigilancia
- Formularios administrativos

---

## Estrategia de evolución

1. Mantener base actual (auth, layout, usuarios)
2. Crear nuevo núcleo de formularios
3. Migrar reemplazo al nuevo modelo
4. Eliminar progresivamente el workflow antiguo

---

## Reglas para agentes (Codex)

Antes de modificar código:

1. Analizar si el cambio mantiene la arquitectura modular
2. Evitar acoplar lógica a un solo formulario
3. Proponer soluciones reutilizables
4. Priorizar claridad sobre complejidad
5. Explicar decisiones si se cambia estructura

---

## Stack

- Laravel
- Blade + Bootstrap + Alpine
- MySQL
- Docker

---

## Resultado esperado

Una plataforma institucional reutilizable, mantenible y escalable.