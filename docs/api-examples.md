Event database API examples
===========================

The event database API uses [API Platform](https://api-platform.com/)
to provide an easy to use API on top of event data. This document
provides some simple examples to get you started using the API, but
for full details you should consult
the [API Platform documentation](https://api-platform.com/) and check
out the underlying technologies
(specifically [JSON-LD](http://json-ld.org/)
and [Hydra](http://www.hydra-cg.com/)).

*Note*: In these examples we assume that the event database API is
running on the url http://event-database-api.vm. Change the url to
match your actual setup.

Interactive API documentation is avaliable on
http://event-database-api.vm/api/docs. Here you can play around with
the API and see which query parameters can be used (example:
http://event-database-api.vm/api/docs#!/Event/getEventCollection).


## Event

### List events

```
curl --silent --request GET --header "Accept: application/ld+json" http://event-database-api.vm/api/events
```

The `Accept` header is used to specify which format to use when returning results. If you prefer XML, accept "application/xml":

```
curl --silent --request GET --header "Accept: application/xml" http://event-database-api.vm/api/events
```

Check out http://event-database-api.vm/api/docs.json (keys "produces") for a list of acceptable Accept headers.

By default, only future events are returned, and to get all events (incl. past events) you must specify a start date query.

To get all events after 2001-01-01, say, use a query like this:

```
curl --silent --request GET --header "Accept: application/ld+json" http://event-database-api.vm/api/events?occurrences.startDate[after]=2001-01-01
```

See http://event-database-api.vm/api/docs#!/Event/getEventCollection for a list of query parameters that can be used to filter and order the list of events.


The event collection (as ld+json) shows a lot of useful information on how to get further details on events and other entities in the result:

```
{
  "@context": "/api/contexts/Event",
  "@id": "/api/events",
  "@type": "hydra:Collection",
  "hydra:member": [
    {
      "@id": "/api/events/1",
      "@type": "http://schema.org/Event",
      …,
      "occurrences": [
        {
          "@id": "/api/occurrences/1",
          "@type": "Occurrence",
          "event": "/api/events/1",
          …,
          "endDate": null,
          "place": {
            "@id": "/api/places/1",
            "@type": "http://schema.org/Place",
            …,
          }
        }
      ]
    },
	…
  ],
  …
}
```

The `@id` of en event in the event collection is a url that we can use to get further information on the event.

### Read event

To get details on a single event, we send a GET result to the `@id` of the events:

```
curl --silent --request GET --header "Accept: application/ld+json" http://event-database-api.vm/api/events/300
```

### Create event

In order to create (and update and delete) events we need to
authenticate. The event database API uses JWT for authentication, so
we have to get a token by POSTing a valid username (api-write) and
password (apipass) to http://event-database-api.vm/api/login_check:

```
curl --silent --request POST http://event-database-api.vm/api/login_check --data _username=api-write --data _password=apipass
```

We can store the token in an environment variable, token, for later use:

```
token=$(curl --silent --request POST http://event-database-api.vm/api/login_check --data _username=api-write --data _password=apipass | php -r 'echo json_decode(stream_get_contents(STDIN))->token;')
echo $token
```

Now we can use the token (in the `Authorization` header) by POSTing to http://event-database-api.vm/api/events:

```
curl --silent --verbose --request POST --header "Authorization: Bearer $token" --header "Content-type: application/ld+json" --header "Accept: application/ld+json" http://event-database-api.vm/api/events --data @- <<'JSON'
{
  "name":"Big bang",
  "description":"The first event",
  "image": "https://dummyimage.com/600x400/000/00ffd5.png",
  "langcode": "en",
  "occurrences": [ {
	"startDate": "2017-03-10T00:08:00+00:00",
	"endDate": "2017-03-10T00:16:00+00:00",
	"place": {
	  "name": "Some place"
	}
  } ]
}
JSON
```

If everything works as planned, the server should respond with `HTTP/1.1 201 Created`.

### Update event

An event can be updated by PUTing data to the event url (`/api/events/2`):

```
curl --silent --verbose --request PUT --header "Authorization: Bearer $token" --header "Content-type: application/ld+json" --header "Accept: application/ld+json" http://event-database-api.vm/api/events/2 --data @- <<'JSON'
{
  "name":"Even bigger bang",
  "description":"The first event",
  "image": "https://dummyimage.com/600x400/000/00ffd5.png",
  "langcode": "en",
  "occurrences": [ {
	"startDate": "2017-03-10T00:08:00+00:00",
	"endDate": "2017-03-10T00:16:00+00:00",
	"place": {
	  "name": "Some place"
	}
  } ]
}
JSON
```

If the update was successful, the server should respond with `HTTP/1.1 200 OK`.


### Delete event

Finally, we can delete the event:

```
curl --silent --verbose --request DELETE --header "Authorization: Bearer $token" http://event-database-api.vm/api/events/2
```

On success, the response should be `HTTP/1.1 204 No Content`.


## Occurrences

@TODO


## Places

@TODO
