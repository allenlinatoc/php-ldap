# PHP-LDAP #

A mixture of executable PHP script and short-hand library for basic LDAP testing and browsing


As of now, this script is being developed and still requires heavy development for more dynamic usage.

I will also be developing a PHP archive under `binary` folder for ease of distribution among users.


For the meantime, you can launch it through `src/ldap` file.

**Format of how this works in terminal**

    $ cd ./php-ldap/src
	$ php ldap --argument1 value1 --argument2 value2 -a -b -c -d

**To view help,**

	$ php ldap [--help|-h]

**This will show the help page:**

	Flags
	  --ssl, -s   If connection will use SSL
	  --help, -h  Show this help
	
	Options
	  --server, -S    LDAP server domain or hostname
	  --port, -P      LDAP server port
	  --domain, -D    Account domain name
	  --basedn, -B    Base DN containing AD users
	  --username, -U  Username of account during bind
	  --password, -P  Password of account during bind
	  --ymlfile, -y   (optional) Arguments YAML file [default: {null}]
	  --filter, -f    (optional) Query filter [default: (objectClass=*)]
	  --attributes    (optional) Comma-separated AD attributes to be fetched
	  --output        (optional) Filename where output will be written [default: {stdio}]
	  --keyattr       (optional) Key attribute per result entry [default: sAMAccountName]


You can also use a YAML file containing the arguments so you don't have to repeat yourself in console.

	---
	filter: (&(objectClass=*)(sAMAccountName=*))
	basedn: CN=Users,DC=my,DC=domain,DC=local
	server: 10.32.0.2
	port: 389
	domain: my.domain.local				# This is something like user@my.domain.local
	username: user
	password: mypassword
	output: ../output.yml				# Filename of output file, results will be in YML format
	attributes: sAMAccountName,company,department,givenName,l

Save it, then invoke it through `--ymlfile` argument.

**Loading arguments file**

Say for example, we save it in a directory that is up one level from `ldap` file's current directory.

Now that should be "../arguments.yml
	
	$ php ldap --ymlfile ../arguments.yml

Obviously, both absolute and canonical paths, and symbolic links can be resolved.