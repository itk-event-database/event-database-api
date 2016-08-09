---
title: API
layout: default
---

# {{ page.title }}

Test the API using username and password to get a token:

```
token=$(curl --silent --request POST http://event-database-api.vm/api/login_check --data _username=api-write --data _password=apipass | sed 's/{"token":"\(.*\)"}/\1/')
echo $token
curl --silent --verbose --header "Authorization: Bearer $token" http://event-database-api.vm/api/events
```

Create an event:

```
curl --silent --verbose --request POST --header "Authorization: Bearer $token" http://event-database-api.vm/api/events --data @- <<'JSON'
{
  "_format":"json",
  "name":"Big bang",
  "description":"The first event",
  "occurrences": [ {
    "startDate": "2000-01-01"
  } ]
}
JSON
```

Get all events:

```
curl --silent --verbose --request GET --header "Authorization: Bearer $token" http://event-database-api.vm/api/events
```
