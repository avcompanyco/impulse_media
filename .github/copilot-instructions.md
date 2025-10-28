
# Laravel 12 Expert Guidelines

## Architecture and Structure
- Implementar arquitectura modular con controladores de acción única usando __invoke
- Utilizar Laravel 12 con InertiaJS y Vue.js en lugar de Blade
- Seguir principios SOLID y patrones de diseño modernos

## Naming Conventions
- Controllers: {Action}{ModuleName}Controller (IndexUserController, StoreUserController)
- FormRequests: {Action}{ModuleName}Request (StoreUserRequest, UpdateUserRequest)
- Traits: Has{Action}{ModuleName} (HasCreateUser, HasUpdateUser, HasDestroyUser)
- Enums: {ModuleName}{Category}Enum (UserStatusEnum, UserRoleEnum)

## Best Practices
- Controladores con una única responsabilidad usando __invoke
- FormRequests para validación y autorización
- Traits para encapsular operaciones de base de datos
- Enums para tipado seguro y valores predefinidos
- Políticas para autorización granular
- Pruebas unitarias y de integración

## Frontend Integration
- Utilizar InertiaJS para comunicación entre Laravel y Vue.js
- Componentes Vue.js con Composition API
- TypeScript para tipado en frontend
- Bootstrap CSS para estilos


# Laravel + InertiaJS + Vue.js 3 + TypeScript + Bootstrap 5 Guidelines

## Tech Stack
- Laravel 12 (Monolito, retorna datos con Inertiajs)
- InertiaJS para comunicación SPA sin API REST
- Vue.js 3 con Composition API
- TypeScript para tipado estricto en frontend
- Bootstrap 5.x para sistema de diseño y componentes UI
- Vite como bundler y herramienta de desarrollo

## Frontend Architecture
- Componentes Vue.js 3 con `<script setup lang="ts">`
- Composition API obligatorio, evitar Options API
- Props e interfaces TypeScript tipadas
- Stores con Pinia para manejo de estado global
- Composables reutilizables para lógica compartida

## InertiaJS Best Practices
- Usar el componente `Link` para navegación programática de @inertiajs/vue3
- Implementar `usePage()` para acceder a props compartidas
- Manejar formularios con `useForm()` de InertiaJS
- Implementar validación reactiva en tiempo real
- Usar `preserveState` y `preserveScroll` apropiadamente

## Bootstrap 5 Integration
- Usar clases Bootstrap para layout (container, row, col)
- Componentes Bootstrap: buttons, cards, modals, forms
- Utilizar sistema de breakpoints responsive de Bootstrap
- Implementar temas personalizados con variables SCSS
- Evitar CSS custom, priorizar utilidades Bootstrap

## TypeScript Standards
- Interfaces para props de componentes Vue
- Types para respuestas de InertiaJS
- Enums para valores constantes compartidos
- Generics para componentes reutilizables
- Strict mode habilitado en tsconfig.json

## Component Structure
resources/js/
resources/js/components
resources/js/composables
resources/js/layouts
resources/js/lib
resources/js/pages
resources/js/types