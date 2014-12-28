# Methods

This file documents all public class methods that you may want to use, organised by namespace.

Brush code is heavily documented, so don't be afraid to open up the source files and look at the comments.

## Accounts

### `Credentials`

Method | Description
--- | ---
`__construct(username:string, password:string)` | Create a new set of credentials from a username and password pair

### `Account`

Method | Description
--- | ---
`__construct(Credentials)` | Create an account from its login credentials
`__construct(key:string)` | Create an account directly from a user session key (more efficient than passing credentials)
`getPastes(Developer, limit:int)` | Retrieve the account's pastes in descending order of date created, up to `limit`

### `Developer`

Method | Description
--- | ---
`__construct(key:string)` | Create a developer directly from an API key

### `User`

A `User` object is returned by a call to `User::fromAccount(Account)`.

Method | Description
--- | ---
`getUsername()` | The user's username
`getAvatarUrl()` | The address of the user's avatar
`getEmail()` | The user's email address
`getWebsiteUrl()` | The address of the user's website
`getLocation()` | The user's location
`getType()` | The user's account type (`User::TYPE_NORMAL` or `User::TYPE_PRO`)
`isPro()` | `getType() == User::TYPE_PRO`

## Pastes

### `Draft`

Method | Description
--- | ---
`::fromOwner(Account, Developer)` | Create a new draft using `Account`'s default settings
`::fromFile(path)` | Create a new draft directly from a file, with title and format detection
`setTitle(string)` | Set the name of the paste
`setContent(string)` | Set the paste content
`setFormat(Format)` | Set the language of the paste's content
`setVisibility(int)` | Set the visibility of the paste, e.g. `Visibility::VISIBILITY_UNLISTED`
`setOwner(Account)` | Set the account that will own the paste
`paste(Developer)` | Submit the draft to Pastebin, and retrieve the `Paste` object

### `Paste`

A `Paste` object is returned when listing pastes, and when `paste()`ing a draft.

Method | Description
--- | ---
`getKey()` | The unique paste key
`getUrl()` | The paste's URL
`getDate()` | When the paste was submitted, as a UNIX timestamp
`getSize()` | The size of the paste in bytes
`getHits()` | The number of unique visitors to the paste
`getExpires()` | A UNIX timestamp of when the paste expires
`isImmortal()` | True if the paste never expires, false otherwise
`getExpiresIn()` | The number of seconds until the paste expires (0 if `isImmortal()`)
`getContent()` | Retrieve the raw paste content
`delete(Developer)` | Delete the paste

### `Trending`

Method | Description
--- | ---
`getPastes(Developer)` | Get the 18 currently trending pastes