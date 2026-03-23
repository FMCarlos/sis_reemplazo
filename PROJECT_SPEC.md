# PROJECT_SPEC.md

## Nombre del Proyecto

Plataforma de Formularios Institucionales

---

## Descripción General

Sistema web interno orientado a la digitalización de procesos administrativos del hospital, permitiendo la creación, gestión y almacenamiento de formularios institucionales con generación automática de documentos PDF.

---

## Objetivo General

Centralizar y optimizar la gestión de formularios institucionales mediante una plataforma modular, escalable y mantenible.

---

## Objetivos Específicos

- Eliminar procesos manuales en papel
- Generar documentos PDF automáticamente
- Mantener trazabilidad de solicitudes
- Permitir consulta y seguimiento
- Facilitar incorporación de nuevos formularios

---

## Alcance Inicial

El sistema debe permitir:

1. Autenticación de usuarios
2. Selección de tipo de formulario
3. Completar formulario
4. Validación de datos
5. Generación de PDF
6. Almacenamiento de solicitud
7. Consulta de solicitudes
8. Descarga de documentos

---

## Tipos de Formularios Iniciales

- Solicitud de reemplazo (primer módulo)
- Horas extraordinarias (futuro)
- Notificación de eventos (futuro)

---

## Arquitectura Funcional

### 1. Form Types
Define los tipos de formularios disponibles.

Campos:
- code
- name
- description
- active

---

### 2. Form Submissions
Representa cada envío realizado.

Campos:
- form_type_id
- submitted_by
- status
- payload_json
- pdf_path
- submitted_at

---

### 3. Form Submission Actions
Registro de eventos del sistema.

Eventos:
- created
- updated
- pdf_generated
- downloaded
- cancelled

---

## Flujo del Sistema

1. Usuario ingresa
2. Selecciona formulario
3. Completa datos
4. Sistema valida
5. Sistema guarda
6. Sistema genera PDF
7. Usuario puede descargar o consultar

---

## Estados del Sistema

Estados simples:

- DRAFT
- SUBMITTED
- CANCELLED

---

## Requisitos No Funcionales

- Sistema web interno
- Acceso por red local
- Soporte múltiples usuarios concurrentes
- Seguridad por roles
- Persistencia de datos
- Generación de PDF confiable

---

## Consideraciones Técnicas

- Uso de Laravel como backend
- MySQL como base de datos
- Docker para despliegue
- Separación de capas (Controller, Service, Domain)
- Generación de PDF desacoplada

---

## Estrategia de Desarrollo

1. Definir núcleo (form_types + form_submissions)
2. Implementar primer formulario (reemplazo)
3. Generar PDF base
4. Implementar listados
5. Habilitar extensibilidad
6. Agregar nuevos formularios

---

## Fuera de Alcance Inicial

- Flujos complejos de aprobación
- Firma electrónica integrada
- Integraciones externas complejas
- Motor 100% dinámico de formularios

---

## Éxito del Proyecto

El sistema será exitoso si:

- Permite generar formularios y PDFs correctamente
- Es fácil agregar nuevos formularios
- Reduce procesos manuales
- Es utilizado por las unidades del hospital

---

## Visión

Convertirse en la plataforma base de gestión de procesos administrativos del hospital.