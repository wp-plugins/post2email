=== post2email ===
Contributors: Ipstenu
Donate link: https://www.wepay.com/donations/halfelf-wp
Tags: email, post, notification
Requires at least: 3.3
Tested up to: 3.7
Stable tag: 1.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Allows the site admin to have all new posts (and only posts) emailed to one email address.

== Description ==

Ever have a day where you want all new posts to result in an email sent to a specific address for notification? Welcome to Post2Email.

Born from the ideas of rss2email, instead of relying wholly on RSS and a cron job to trigger email, Post2Email instead hooks into WP to send emails from WordPress itself. Instead of reinventing the wheel, the plugin checks what you set for RSS (show full text or excerpt) to decide what to use for message content.

What I'm contemplating adding:
* Ability to say "no, not this post"
* Allow options for different post types to get emailed

== Installation ==

Install per-usual

Configuration Options are on Settings -> Reading

== Frequently asked questions ==

= Why only one email? =

To limit your ability to spam. By restricting you to one address, you won't be able to just send emails to the whole world without them agreeing to it first. If you really need to send to multiple people, you want something like [Subscribe2](http://wordpress.org/extend/plugins/subscribe2/) or [Notifly](http://wordpress.org/extend/plugins/notifly). 

Better yet, use the right tool for the job: A mailing list. And then you can have this plugin email the mailing list. That's what I do.

= What can I customize? =

The 'to' email address (defaults to your admin)
The 'from' email address (defaults to wordpress@yourdomain.com)
The 'from' name (defaults to your blog's name)
The 'Read more:' content (defaults to 'Read more:')

= How do I make the email send out post excerpts? =

The email content is determined by your RSS choices. If you have RSS set to 'excerpt' then your emails will be excerpts. If you use a custom excerpt, it will use that. Don't have one? It grabs the first 55 words. (Obviously full post is full post.)

= Can I flag specific posts as not getting emailed? =

Not right now. By default, no private posts will be emailed, though, so you can use private and password protected to hide them.

If you change a post from private to publish, though, it will email then.

= Why doesn't it send emails for pages and custom-post-types? =

This is by design. I thought about adding it, but Jetpack doesn't and I've come to realize that's a good thing. Pages and CPTs aren't posts. It's a possibility I might do this later, but right now I don't feel the need to overcomplicate.

== Screenshots ==

1. Settings

== Changelog ==

= Version 1.2 =
* 21 May 2013 by Ipstenu
* Better handling of content
* Poppin' tags (strip_tags() really)
* De-texturizing email titles
* Serialization
* Translate all the things!

= Version 1.1 = 
* 10 May 2013 by Ipstenu
* Data sanitization
* Adding in ability to customize 'Read More' prefix.
* Better handling of plugin defaults

= Verision 1.0 =
* 9 May 2013 by Ipstenu
* All New! All Fun!

== Upgrade notice ==
