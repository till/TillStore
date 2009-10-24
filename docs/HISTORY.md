## The History

I went to [nosqlberlin][] on Thursday, October 22nd, 2009. It was a pretty cool
event and the talks were people presenting their new database. Among those _new_
databases Redis, CouchDB, Riak and MongoDB (all pretty cool and _awesome_
projects!).

[nosqlberlin]: http://nosqlberlin.de/

Redis and Riak are what people call key-value-stores. Not exactly a database, but a
more or less dumb store for small sets of data. Think memcached -- those two are
right down that alley. Which brings me to TillStore!

Previously, I had joked with a friend about my own key-value-store -- TillStore, but
I had actually never ever implemented it.

When I got home from nosqlberlin on Thursday night, I realized I had lost the keys to
my apartment. So even though they were found and one of my (most awesome) neighbours
had deposited them at the bakery downstairs, I wish shit out of luck. The backery was
closed at 10 PM.

So instead of freezing my butt off outside, I went to the next Internet cafe (across
the street from where I live), did some work, and started hacking on TillStore. So
not even 10 work hours later, this is the initial 0.1.0-alpha release (BE GENTLE).

People may say, "WTF?!". Yeah, correct. I wrote a key-value-store in PHP. But I did
not write it to power Yahoo!, I wrote it as a proof of concept, and because I can.

Deal with it. And enjoy! :-)
