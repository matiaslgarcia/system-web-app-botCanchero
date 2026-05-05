# Diccionario del Proyecto BotCanchero

## Arquitectura y Estructura

### Carpetas Principales
- **/api/**: Contiene la API REST para la comunicación con aplicaciones externas
- **/app/**: Aplicación web principal con interfaces de usuario y lógica de negocio
- **/error/**: Gestión de errores del sistema
- **/cronjob/**: Tareas programadas para ejecución automática
- **/phpMyAdmin/**: Herramienta de administración de base de datos

### Subcarpetas Importantes
- **/api/v2/lib/**: Clases específicas para el funcionamiento de la API
- **/app/lib/**: Clases PHP que implementan la lógica de negocio del sistema
- **/app/inc/**: Componentes de interfaz reutilizables (headers, footers, sidebar)
- **/app/assets/**: Recursos estáticos (CSS, JS, imágenes)
- **/app/upload/**: Directorio para almacenar archivos subidos al sistema

### Archivos de Configuración
- **config.php**: Configuración de conexión a la base de datos y URLs del sistema
- **config_sample.php**: Ejemplo de configuración para despliegue
- **int.php**: Inicialización de clases y configuración del sistema

## Base de Datos

### Tablas Principales
- **soccer_field**: Canchas de fútbol disponibles
- **booking**: Reservas realizadas por los clientes
- **customers**: Información de los clientes que realizan reservas
- **users**: Usuarios administrativos del sistema
- **schedules**: Horarios disponibles para reservas
- **payment**: Registro de pagos realizados

### Tablas de Relación
- **soccer_field_services**: Vincula canchas con servicios adicionales
- **booking_logs**: Registro de cambios en las reservas (auditoría)
- **field_schedule**: Horarios específicos por cancha

### Tablas de Soporte
- **province/city**: Información geográfica
- **payment_preference**: Configuraciones de pago por usuario
- **api_token**: Tokens de acceso para la API

## Clases Principales

### API
- **ClassApi**: Gestiona peticiones y respuestas de la API
- **ClassAut**: Manejo de autenticación para la API
- **ClassBooking**: Lógica de reservas de canchas
- **ClassCanchas**: Gestión de campos de fútbol
- **ClassPayment**: Procesamiento de pagos
- **ClassServices**: Servicios adicionales para canchas

### Aplicación Web
- **ClassConexion**: Manejo de conexiones a la base de datos
- **ClassTheme**: Gestión de elementos visuales
- **ClassUsers**: Administración de usuarios
- **ClassCustomers**: Gestión de clientes
- **ClassInvoices**: Generación de facturas y reportes
- **ClassMobex/ClassMercadoPago**: Integración con pasarelas de pago

## Flujos de Negocio

### Reserva de Cancha
1. Cliente selecciona cancha
2. Elige fecha y hora disponible
3. Completa información personal
4. Confirma reserva
5. Realiza pago (opcional según configuración)
6. Recibe confirmación

### Administración de Canchas
1. Propietario registra nueva cancha
2. Configura horarios disponibles
3. Establece precios y servicios
4. Activa la cancha para reservas

### Proceso de Pago
1. Generación de preferencia de pago
2. Redirección a pasarela (Mobbex/MercadoPago)
3. Procesamiento del pago
4. Callback de confirmación
5. Actualización del estado de reserva
6. Generación de comprobante

## Conceptos Técnicos

### Estados de Reserva
- **1**: Pendiente de confirmación
- **2**: Confirmada
- **3**: Cancelada

### Formatos de API
- **Entrada**: JSON vía POST
- **Salida**: JSON estructurado con datos o mensajes de error
- **Autenticación**: Básica HTTP o tokens API

### Integraciones Externas
- **Mobbex**: Procesador de pagos principal
- **MercadoPago**: Procesador de pagos alternativo

## Problemas Conocidos y Áreas de Mejora

### Seguridad
- Credenciales expuestas en archivos de configuración
- Falta de sanitización consistente de entradas
- Uso limitado de CSRF tokens

### Arquitectura
- Uso excesivo de métodos estáticos
- Mezcla de lógica de negocio y presentación
- Falta de documentación del código

### Base de Datos
- Optimización de índices necesaria
- Algunas tablas con campos redundantes

### Rendimiento
- Sin sistema de caché implementado
- Consultas SQL potencialmente ineficientes en alta carga

## Conclusión

BotCanchero es un sistema de gestión de canchas de fútbol con funcionalidades completas de reserva y pago. Presenta una arquitectura tradicional de PHP con clases orientadas a objetos, pero con algunas deficiencias en términos de seguridad y mantenibilidad.

A pesar de estas observaciones, el sistema parece funcional y cumple con los requisitos básicos para la gestión de reservas de canchas de fútbol, integración de pagos y administración de usuarios.