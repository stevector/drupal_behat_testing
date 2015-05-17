#!/bin/bash
# This file is part of the original drupalci proof of concept development, and
# as such, has been deprecated.  Web-based executable containers are expected
# to start up apache directly.
#
# eg.  CMD = ['apache2', '-D', 'FOREGROUND']
read pid cmd state ppid pgrp session tty_nr tpgid rest < /proc/self/stat
trap "kill -TERM -$pgrp; exit" EXIT TERM KILL SIGKILL SIGTERM SIGQUIT

source /etc/apache2/envvars
apache2 -D FOREGROUND
