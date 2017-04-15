# eZ Publish BDD Testing - REST API

Take a look to Sentences and/or ContentManager files in Behat bundle for generic
sentences.

For the following documentation you should remember:

- Words inside angular brackets ([]) are sentence definitions
- Words inside less and greater characters (<>) are user input

## Request creation ( When sentences )

To make the request we need to build it through the way and send it when it's
ready.

### Add authentication

It work's as the generic sentences:
```
    I am an anonymous visitor
    I am logged as an "<user-group>"
    I am logged as "<specific-user>"
```
```<user-group>``` can be:
- Administrator
- Editor
- Member
( anonymous shouldn't be an option since there shouldn't be a log in for
anonymous users )

### Make a request
```
    I make a "<type>" request to "<url>"
```
```<type>``` can be:
- GET
- POST
- PATCH
- DELETE

```<url>``` should be the url of the action intended to be done without the server name
and Rest prefix.

### Add an header:
```
    I add to request the header "<header>" with "<value>"
    I add to request the headers:
        | header | value |
```
```<header>``` can be:
- Accept
- Content-Type
- ...

```<value>``` should be complete with the header prefix: ``` application/vnd.ez.api. ```

### Add body:
```
    I add to request body a "<action> <content>" in "<type>"
    I add to request body a "<action> <content>" in "<type>" with:
        | field | value1 | ... | valueN |
    I add to request body a "<action> <content>" in "<type>" with only:
        | field | value1 | ... | valueN |
    I add to request body:
        """
            # raw xml or json goes here
        """
```
```<content>``` need to be defined on the programming part in xml and json.
```<content>``` can be:
- ContentTypeGroup
- ContentType
- Content
- Section
- User
- UserGroup
- ...

```<action>``` can be:
- Create
- Delete
- Update
- ...

```<type>``` can be:
- xml
- json

In the second and third sentences we can define extra values for the xml/json
made on programming part.

The "only" word on the sentence will stop the "auto-complete" data on the
programming part to generate data for the ```<content>```.

### Add *invalid* body

Also for body we can have invalid inputs:
```
    I add to request body an invalid "<action> <content>" in "<type>"
    I add to request body an invalid "<action> <content>" in "<type>" with:
        | field | value1 | ... | valueN |
```

However this should be used with precaution since it can bring incoherence cause
the "invalid" need to be defined on the programming part.
ex:
    on the programming part we do not define a required field (to make it
    invalid), for the sake of the example it will be "identifier"
    and on the sentence we do:
```
    I add to request body an invalid "Create ContentTypeGroup" in "xml" with:
        | field      | value |
        | identifier | foo   |
```
    the body would then be valid, making the scenario invalid.

### Send the request

The request needs to be sent to make sure that everything needed to define wash
already defined.

```
    I send the request
```

## Response assertion ( Then sentences )

### Assert response code:
```
    I see "<code>" response code
```
```<code>``` can be the 20X, 30X, 40X or 50X. And it can have the code title in front
here are some examples:
- 200
- 200 OK
- 404 NOT FOUND
- 307 TEMPORARY REDIRECT
- 400 BAD REQUEST
- ...

*IMPORTANT*: the title won't actually be tested only code, so the following will
also be valid:
- 201 NOT FOUND
- 500 OK
- 401 REDIRECT
( although the above are not correct the sentence will only assert the code )

### Assert header

```
    I see response header "<header>" with "<value>"
    I see response headers:
        | header | value |
```

```<header>``` and ```<value>``` are the same defined on the request headers sentences

### Assert body
```
    I see response body in <type>
    I see response body with <content>
    I see response body with <content> in <type>
    I see response body with:
        """
            # raw xml or json goes here
        """
```

Also on body the ```<content>``` and ```<type>``` are the same in the request body sentences
Here verify that the ```<type>``` and ```<content>``` can be asserted isolated
