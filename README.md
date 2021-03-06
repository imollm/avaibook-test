[![Deploy PHP to Azure Web App](https://github.com/imollm/avaibook-test-ci-cd/actions/workflows/pipeline.yml/badge.svg)](https://github.com/imollm/avaibook-test-ci-cd/actions/workflows/pipeline.yml)

# Reto: Servicio para gestión de calidad de los anuncios

Este repositorio contiene un API parcialmente desarrollada para desarrollar un servicio que se encargue de medir la calidad de los anuncios. Tu objetivo será implementar las historias de usuario que se describen más adelante.

Los supuestos están basados en un hipotético *equipo de gestión de calidad de los anuncios*, que demanda una serie de verificaciones automáticas para clasificar los anuncios en base a una serie de características concretas.

## Historias de usuario

* Yo, como encargado del equipo de gestión de calidad de los anuncios quiero asignar una puntuación a un anuncio para que los usuarios de idealista puedan ordenar anuncios de más completos a menos completos. La puntuación del anuncio es un valor entre 0 y 100 que se calcula teniendo encuenta las siguientes reglas:
    * Si el anuncio no tiene ninguna foto se restan 10 puntos. Cada foto que tenga el anuncio proporciona 20 puntos si es una foto de alta resolución (HD) o 10 si no lo es.
    * Que el anuncio tenga un texto descriptivo suma 5 puntos.
    * El tamaño de la descripción también proporciona puntos cuando el anuncio es sobre un piso o sobre un chalet. En el caso de los pisos, la descripción aporta 10 puntos si tiene entre 20 y 49 palabras o 30 puntos si tiene 50 o mas palabras. En el caso de los chalets, si tiene mas de 50 palabras, añade 20 puntos.
    * Que las siguientes palabras aparezcan en la descripción añaden 5 puntos cada una: Luminoso, Nuevo, Céntrico, Reformado, Ático.
    * Que el anuncio esté completo también aporta puntos. Para considerar un anuncio completo este tiene que tener descripción, al menos una foto y los datos particulares de cada tipología, esto es, en el caso de los pisos tiene que tener también tamaño de vivienda, en el de los chalets, tamaño de vivienda y de jardín. Además, excepcionalmente, en los garajes no es necesario que el anuncio tenga descripción. Si el anuncio tiene todos los datos anteriores, proporciona otros 40 puntos.

* Yo como encargado de calidad quiero que los usuarios no vean anuncios irrelevantes para que el usuario siempre vea contenido de calidad en idealista. Un anuncio se considera irrelevante si tiene una puntación inferior a 40 puntos.

* Yo como encargado de calidad quiero poder ver los anuncios irrelevantes y desde que fecha lo son para medir la calidad media del contenido del portal.

* Yo como usuario de idealista quiero poder ver los anuncios ordenados de mejor a peor para encontrar fácilmente mi vivienda.

## Consideraciones importantes

En este proyecto te proporcionamos un pequeño *esqueleto* escrito en PHP 8 usando Symfony Flex.

En dicho *esqueleto* hemos dejado varios Controllers y un Repository en el sistema de ficheros como orientación. Puedes crear las clases y métodos que consideres necesarios.

Podrás ejecutar el proyecto utilizando la configuración de Docker que dejamos en el mismo *esqueleto* e instalando a través de composer los paquetes necesarios.

**La persistencia de datos no forma parte del objetivo del reto**. Si no vas a usar el esqueleto que te proporcionamos, te sugerimos que la simplifiques tanto como puedas (con una base de datos embebida, "persistiendo" los objetos en memoria, usando un fichero...). **El diseño de una interfaz gráfica tampoco** forma parte del alcance del reto, por tanto no es necesario que la implementes.

**Nota:** No estás obligado a usar el proyecto proporcionado. Si lo prefieres, puedes usar cualquier otro lenguaje, framework y/o librería. Incluso puedes prescindir de estos últimos si consideras que no son necesarios. A lo que más importancia damos es a tener un código limpio y de calidad.

### Requisitos mínimos

A continuación se enumeran los requisitos mínimos para ejecutar el proyecto:

* PHP 8
* Symfony Local Web Server o Nginx.

## Criterios de aceptación

* El código debe ser ejecutable y no mostrar ninguna excepción.

* Debes proporcionar 3 endpoints: Uno para calcular la puntuación de todos los anuncios, otro para listar los anuncios para un usuario de idealista y otro para listar los anuncios para el responsable del departamento de gestión de calidad.

## Solución propuesta

### Endpoints
* Se ha construido un API REST con 4 endpoints:

1. **/healthcheck**
    * <em>Endpoint para comprobar que la API funciona.</em>
2. **/api/v1/calculate-scores**
    * <em>Endpoint para calcular las puntuaciones de los anuncios.</em>
3. **/api/v1/irrelevant-ads**
    * <em>Endpoint para recuperar los anuncios con puntuación menor a 40 puntos. También proporciona la media de todos los tipos de anuncio.</em>
4. **/api/v1/quality-ads**
    * <em>Endpoint para recuperar los anuncios con puntuación mayor a 40 puntos, ordenados de mayor a menor puntuación.</em>

Se ha seguido una implementación guiada por tests TDD.

### Componentes implementados:
  * **src/Controller**
    * <em>CalculateScoresController</em>
      * Controlador que recibe las peticiones del endpoint 2. 
    * <em>HealthCheckController</em>
      * Controlador que recibe las peticiones del endpoint 1.
    * <em>IrrelevantListingController</em>
      * Controlador que recibe las peticiones del endpoint 3.
    * <em>QualityListingController</em>
      * Controlador que recibe las peticiones del endpoint 4.
  * **src/DataFixtures**
    * <em>AppFixtures</em>
      * Componente que guarda los datos proporcionados por el ejercicio en la base de datos.
    * <em>FakeData</em>
      * Componente que proporciona los datos proporcionados por el ejercicio.
  * **src/DataFixtures**
    * <em>Ad</em>
      * Componente que representa la entidad anuncio.
    * <em>Picture</em>
      * Componente que representa la entidad imagen.
  * **src/Repository**
    * <em>AdRepository</em>
      * Componente que interactua con la base de datos para la persistencia y recuperación de anuncios.
    * <em>PictureRepository</em>
      * Componente que interactua con la base de datos para la persistencia y recuperación de imágenes.
  * **src/Rules**
    * <em>DescriptionRules</em>
      * Componente con las puntuaciones declaradas en las reglas de negocio sobre las descripciones de los anuncios.
    * <em>PictureRules</em>
      * Componente con las puntuaciones declaradas en las reglas de negocio sobre la calidad de las imágenes.
  * **src/Services**
    * <em>CalculateScoresService</em>
      * Componente encargado de hacer el cálculo de las puntuaciones de todos los anuncios guardados en la base de datos.

### Diagramas de actividad

[![Diagramas de actividad para la recuperación de anuncios](/docs/diagram_activity.jpg)](/docs/diagram_activity.jpg)

### Iniciar proyecto

#### Instalación con Docker

1. Baja el repositorio
```shell
$ git clone https://github.com/imollm/avaibook-test.git 
```
2. Situate dentro de la carpeta docker
```shell
$ cd avaibook-test/docker/
```
3. Levantamos los servicios PHP y DATABASE
```shell
$ docker-compose up -d --build
```
4. Instalamos dependencias 
```shell
$ docker exec -it php composer install
```
5. Comprueba que la api funciona
```shell 
$ curl -i http://localhost:8081/healthcheck
```
6. Guardamos los datos en la base de datos
```shell
$ docker exec -it php doctrine:fixtures:load
```

#### Ejecuta los tests

```shell
$ docker exec -it php composer test
```

#### Prueba los Endpoints

```shell 
curl http://localhost:8081/api/v1/calculate-scores
```
```shell 
curl http://localhost:8081/api/v1/irrelevant-ads
```
```shell 
curl http://localhost:8081/api/v1/quality-ads
```
#### Puntuaciones resultantes

```shell
+-------+-------+
| Ad id | Score |
+-------+-------+
|     1 |     0 |
|     2 |    90 |
|     3 |    20 |
|     4 |    80 |
|     5 |    75 |
|     6 |    50 |
|     7 |     0 |
|     8 |    25 |
+-------+-------+
```
