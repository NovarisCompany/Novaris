
# Modelo de Datos

## Modelo Entidad-Relación (MER)
Las entidades principales identificadas en el [[MER.pdf]] son [[13]]:
- **USUARIO**: Posee un rol y pertenece a un área.
- **TICKET**: Reportado por un usuario y atendido por un técnico.
- **EQUIPO**: Afectado por un ticket y asignado en un historial.
- **HISTORIAL_ASIGNACION**: Registra la trazabilidad de equipos.

## Modelo Relacional
Estructura de tablas clave [[14, 15]]:
- `USUARIO` (PK id_usuario, FK id_rol, FK id_area)
- `EQUIPO` (PK id_equipo, FK id_area)
- `TICKET` (PK id_ticket, FK id_usuario, FK id_tecnico, FK id_equipo)
- `TECNICO` (PK id_tecnico, FK id_usuario)

---
*Relacionado:* [[Gestión de Inventario]], [[Mesa de Ayuda]].
5. Archivo: Gestión del Proyecto.md
Cronograma y pautas de entrega.
# Gestión del Proyecto

## Cronograma (Diagrama de Gantt)
El desarrollo se divide en fases desde mayo hasta octubre de 2026 [[16, 17]]:
- **Fase 1 (Julio):** Primera entrega (Relevamiento y [[Modelo de Datos]]) [[8, 18]].
- **Fase 2 (Septiembre):** Segunda entrega (Actualización de repositorio y [[Infraestructura y Sistemas|backups]]) [[18, 19]].
- **Fase 3 (Octubre):** Entrega final (Testing y manuales) [[18, 19]].

## Entregas Obligatorias
Cada instancia es un avance que conforma un **documento único e integrado** [[20, 21]]. El uso de herramientas como [[Trello]] o matrices RACI ayuda a definir responsabilidades [[22, 23]].

---
*Relacionado:* [[Equipo y Roles]], [[Fundamentación Sociológica]].
6. Archivo: Fundamentación Sociológica.md
El impacto del proyecto en la comunidad institucional [
].
# Fundamentación Sociológica

## Impacto en la Comunidad
El proyecto no solo es una solución técnica, sino una intervención en el entorno laboral de la institución [[25]].

- **Problema Detectado:** Ineficiencia por registros manuales y planillas independientes [[1]].
- **Objetivo Social:** Reducir la frustración del personal administrativo y técnico mediante la optimización de los tiempos de respuesta [[26]].
- **Cambio Organizacional:** La centralización de la información mejora el clima laboral y la organización del trabajo diario [[26, 27]].

---
*Visualización:* [[Mapa Conceptual de Sociología]] (Lectura vertical narrativa).