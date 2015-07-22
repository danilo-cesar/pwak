# PWAK Framework Quick Start Guide #


  * License: [MIT License](http://opensource.org/licenses/mit-license.php)
  * Copyright: 2003-2008, Ateor


## Installation ##

Download PWAK
([http](http://code.google.com/p/pwak/downloads/list),
[svn](http://code.google.com/p/pwak/source/checkout))

pear install [Console\_CommandLine](http://pear.php.net/package/Console_CommandLine)


### First steps ###
#### create project ####

```
  $ cd /path/to/framework
  $ php bin/framework.php create_project /path/to/my/project
```

Edit the project configuration file:

```
  $ cd /path/to/my/project
  $ cp config/project.conf.dist config/project.conf
  $ vim config/project.conf
```

#### create project xml ####

Edit the project.xml file in /path/to/my/project/config/xml/ to add your
entities.

```
  $ cd /path/to/my/project
  $ vim config/xml/project.xml
```

Entity description:

```
  <entity name="EntityName">
    <property type="string" name="PropertyName">
    <property type="const" name="ConstName">
      <const name="CONSTNAME1" value="0" />
      <const name="CONSTNAME2" value="1" />
    </property>
  </entity>
```

The entity attributes are:

| **attribute** | **type** |**required**| **default**   |  **description**                |
|:--------------|:---------|:-----------|:--------------|:--------------------------------|
| **feature**   | list     | no         | nothing       | used to define the controllers mapping |
| **label**     | string   | no         | empty         | entity label                    |
| **name**      | string   | yes        |               | entity name                     |
| **parent**    | string   | no         | none          | parent entity                   |
| **tablename** | string   | no         | entity name   | database table name             |

The property attributes are:

| **attribute**      | **type**  |**required**|**default**| **description**                |
|:-------------------|:----------|:-----------|:----------|:-------------------------------|
| **add\_button**     | boolean   | no         | false     | used to add a button to add foreign key objects. |
| **bidirectional**  | boolean   | no         | false     | used to reflexive foreign keys, from entity to entity  |
| **class**          | string    | yes if   type is foreign key |           | the entity name of the type is entity linked with the foreign key |
| **default**        | mixed     | no         | empty     | default values of the  property|
| **emptyfordelete** | boolean   | no         | false     |                                |
| **field**          | string    | no         |           |                                |
| **i18n**           | boolean   | no         | false     | if true the getters used  gettext to translate the property |
| **inplace\_edit**   | boolean   | no         | false     | true to edit the foreign key attributes in the add/edit controller |
| **label**          | string    | no         | empty     |                                |
| **length**         | string    | no         |           |                                |
| **linkfield**      | string    | no         |           |                                |
| **linktable**      | string    | no         |           |                                |
| **multiplicity**   |           | no         |           |                                |
| **name**           | string    | yes        |           |                                |
| **navigable**      | boolean   | no         | false     | only for the foreign key       |
| **navigablename**  | string    | no         |           |                                |
| **ondelete**       |           | no         |           |                                |
| **required**       | boolean   | no         | false     | the property must be provide in add/edit controllers |
| **section**        | string    | no         | empty     |                                |
| **shortlabel**     | string    | no         | empty     |                                |
| **type**           | string    | yes        |           | see _properties types_         |
| **unique**         | boolean   | no         | false     | used to check that the property is unique |
| **usedby**         | list      | no         | nothing   | used to define the controllers mapping |

The const attributes are:

| **attribute** | **type**  | **required** | **default** | **description**                |
|:--------------|:----------|:-------------|:------------|:-------------------------------|
| **label**     | string    |  no          |  empty      | the const label used in the controllers |
| **name**      | string    |  yes         |             | used to define the const name  |
| **value**     | mixed     |  yes         |             | the const value                |

properties types:

  * **int**
  * **float**
  * **bool**
  * **string**
  * **const**
  * **foreignkey**
  * **manytomany**
  * **email**
  * **text**
  * **html** (like text but the add/edit controller use a wysiwyg editor)
  * **file** (to upload a file)
  * **image** (to store image)

**generate models**

```
  $ cd /path/to/framework
  $ php bin/framework.php gen_models /path/to/my/project
```

**generate sql**

```
  $ php framework.php gen_sql /path/to/my/project
```

**create database**

```
  $ mysql -uroot -p
  $ Enter password:
  mysql> create database myDatabaseName;
  mysql> GRANT ALL PRIVILEGES ON myDatabaseName.* TO myUser identified by 'myPassword';
  mysql> GRANT ALL PRIVILEGES ON myDatabaseName.* TO myUser@localhost identified by 'myPassword';
  mysql> GRANT ALL PRIVILEGES ON myDatabaseName.* TO myUser@'%' identified by 'myPassword';
  mysql> FLUSH PRIVILEGES;
  mysql> quit
  $ mysql -umyUser -p myDatabaseName < /path/to/my/project/config/sql/project.sql
```

**create menu**

Edit the menu.xml in /path/to/my/project/config/xml/ :

```
  $ cd /path/to/my/project
  $ vim config/xml/menu.xml
```

Example:

```
  <menu>
    <item title="Menu1" link="" description="">
      <item title="SousMenu1" link="" description="" />
      <item title="SousMenu2" link="" description="" />
    </item>
    <item title="Menu2" link="" description="" />
  </menu>
```