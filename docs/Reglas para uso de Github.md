# Reglas para Trabajar en el Repositorio GitHub

#### Paso 1: Actualizar tu Repositorio Local
- Correr los comandos
``` bash
git checkout
git pull origin develop
```
- Así se sincronizan con la última versión disponible del Repositorio GitHub

#### Paso 2 : Crear una Rama Derivada de Develop
- Correr el comando
``` bash
git checkout -b backend-Login
```
- En lugar de `backend-Login`, ponerle un nombre propio a su Rama
- \<Lado del Proyecto> - \<Página/Funcionalidad en la que trabajan>

#### Paso 3 : Trabajar
- Cada equipo trabaja exclusivamente en su carpeta

#### Paso 4 : Hacer el Commit y Push
- Una vez finalizado el trabajo de ese día, correr los comandos
``` bash
git add .
git commit -m "backend: Agrega modelo FunctionalKit"
```
- Para añadir sus cambios al su repositorio local
- Luego correr el comando
``` bash
git push -u origin nombre-rama
```
- Así se actualiza el GitHub con la nueva rama y sus cambios

#### Paso 5 : Hacer el Pull-Request
- Luego en GitHub se debe hacer un pull-request cuando consideren que su rama está lista para ir al código central en develop
- Debe ser aprobado por el administrador para que entre, así evitamos accidentes
- Una vez aprobado el código entrará a la rama Develop listo para ser usado por los demás
- Si ya terminaste con una rama (No la usarás más) mejor eliminarla para evitar saturación