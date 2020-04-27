#include <stdio.h>
#include <string.h>

int main(int argc, char **argv) {
	char cmd[255];

    if (argc < 2) // no arguments were passed
    {
        // do something
    }

    if (strcmp("integral", argv[1]) == 0)
    {
        //or something
    }
    else
    {
        //sprintf(cmd, "nohup php5 /var/www/domocan/bin/recv.php %s > nohup.out 2>&1 &", argv[1]); // " > /dev/null 2>&1"
		sprintf(cmd, "php5 /var/www/domocan/bin/recv.php %s &", argv[1]); // " > /dev/null 2>&1"
		system(cmd);
    }

}