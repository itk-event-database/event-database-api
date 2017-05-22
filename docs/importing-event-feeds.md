Importing event feeds
=====================

The event database API can import events from feeds.

Currently, importing [JSON](https://en.wikipedia.org/wiki/JSON) and [XML](https://en.wikipedia.org/wiki/XML) feeds is supported.

## Event feed requirements

The following requirements must be fulfilled before an event feed can be imported into the event database.

* The feed must be accessible via an HTTP GET request.
* The feed must be a list of [*events*](#events).
* Each event must contain one or more [*occurrences*](#occurrences).

The property names used are only examples, i.e. if you have an existing event feed using
different names, but providing the nescessary data, the event feed can be imported.

### Events

Each event in the feed must provide the following data.

An event must either provide a start time and an end time or a list of [*occurrences*](#occurrences) each providing a start and end time.

| Property    | Type                      | Required | Comment |
|-------------|---------------------------|----------|---------|
| id          | integer or string         | Yes      | A unique identifier for the event. Used to detect if an event is new or an update. |
| name        | string                    | Yes      | Name of the event |
| image       | url                       | Yes      | Image for the event |
| description | string                    | Yes      | Can contain HTML. |
| excerpt     | string                    | No       | Short description. If not set, it will be generated from "description" |
| occurrences | list of occurrences       | Yes†     | † See [Note on occurrences](#note-on-occurrences). |
| tags        | string or list of strings | No       | If "tags" is a string it should use a delimiter to separate tags (e.g. "Music, Heavy metal" using comma as delimiter). On import a string will be converted to a list of strings. |

#### Note on occurrences

If all events in the feed have only one occurrence, the occurrence properties can be added to the event itself, i.e. you don't have to add a list of occurrences to events in the feed if it's not already there.

**Example**: An example on how to provide start and end times with "occurrences" and without "occurrences". After import both events will be exactly identical.

```
{
  "id": "event-87",
  "name": "The first event (with occurrences)",
  "occurrences": [ {
    "starttime": "2017-03-16T09:00:00+00:00",
    "endttime": "2017-03-16T17:00:00+00:00",
    …
  } ],
  …
}
```

```
{
  "id": "event-87",
  "name": "The first event (without occurrences)",
  "starttime": "2017-03-16T09:00:00+00:00",
  "endttime": "2017-03-16T17:00:00+00:00",
  …
}
```

### Occurrences

An occurrence must provide the following data.

| Property    | Type       | Required | Comment |
|-------------|------------|----------|---------|
| starttime   | datetime\* | Yes      | The start time of the occurrence (event). |
| endtime     | datetime\* | Yes      | The start time of the occurrence (event). Must be after "starttime". |
| place       | place      | Yes‡     | ‡ See [Note on place](#note-on-place). |

\* a "datetime" must be a string representation of a time and date. We use [ISO 8601](https://en.wikipedia.org/wiki/ISO_8601), e.g. "2017-03-16T17:00:00+00:00". If there is no timezone registered we will "guess" that the timezone is copenhagen-time, which is a guess and therefore we prefer using ISO8601. 

#### Note on place

Rather than putting place data into the "place" property of an occurrence, it can be put directly on the occurrence.

**Example**: An example on how to provide start and end times with "place" and without "place". After import both events will be exactly identical.

```
{
  "starttime": "2017-03-16T09:00:00+00:00",
  "endttime": "2017-03-16T17:00:00+00:00",
  "place": {
    "name": "Some place",
    …
  },
  …
}
```

```
{
  "starttime": "2017-03-16T09:00:00+00:00",
  "endttime": "2017-03-16T17:00:00+00:00"
  "place_name": "Some place",
  …
}
```

As mentioned in [Note on occurrences](#note-on-occurrences), properties on occurrences can be added to events, and, by extension, this means that place properties can be added to events as well.

**Example**: The event

```
{
  "id": "event-87",
  "name": "The first event (with occurrences)",
  "occurrences": [ {
    "starttime": "2017-03-16T09:00:00+00:00",
    "endttime": "2017-03-16T17:00:00+00:00",
    "place": {
      "name": "Some place",
      …
    },
    …
  } ],
  …
}
```

is identical to this event:

```
{
  "id": "event-87",
  "name": "The first event (with occurrences)",
  "starttime": "2017-03-16T09:00:00+00:00",
  "endttime": "2017-03-16T17:00:00+00:00",
  "place_name": "Some place",
  …
}
```


### Places

A place must provide the following data.

| Property      | Type   | Required | Comment |
|---------------|--------|----------|---------|
| name          | string | Yes      | Name of the place, e.g. "Musikhuset Aarhus". |
| streetaddress | string | Yes      | |
| city          | string | Yes      | |
| zipcode       | string | Yes      | |
