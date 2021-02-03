# Teltonika Server

This is a PHP application to decode and store the GPS and IO information received from Teltonika devices.
Also it can be used for sending commands based on Codec12 protocol.


# Teltonika_devices_names

FMBxx_%%last 7 chars of imei%%


# Links

* https://wiki.teltonika-gps.com/view/Codec#Codec_8 - GPS/IO data protocol
*  https://wiki.teltonika-gps.com/view/FMB_AVL_ID - Events List
* https://wiki.teltonika-gps.com/view/Codec#Codec_12 - remote control using SMS/GPRS-commands protocol
* https://wiki.teltonika-gps.com/view/FMB900_SMS/GPRS_Commands - list of proccessable commands
* http://terminal.beeline.ru/upl_instructions/rukovodstvo-polzovatelya-teltonika-fmb900.pdf - Teltonika FMB900 MANUAL RUS


# Dependencies

* ReactPHP: https://reactphp.org/
* Medoo: https://medoo.in


# How to use
First: create appConfig.json, you may use something like bottom bellow: \
file://appConfig.json
```
{
    "geo_host": "17.10.10.8",
    "geo_port": "19737",
    "controll_host": "127.0.0.1",
    "controll_port": "19736",
    "db_user": "taxi",
    "db_pass": "taxi",
    "db_name": "taxi",
    "db_host": "127.0.0.1"
}