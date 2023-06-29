# Logstore HTTP Referrer

Store the HTTP referrer URL in the logstore table when it's available. This builds on top of the core plugin logstore standard.

## Installing via uploaded ZIP file

1. Log in to your Moodle site as an admin and go to _Site administration >
   Plugins > Install plugins_.
2. Upload the ZIP file with the plugin code. You should only be prompted to add
   extra details if your plugin type is not automatically detected.
3. Check the plugin validation report and finish the installation.

## Installing manually

The plugin can be also installed by putting the contents of this directory to

    {your/moodle/dirroot}/admin/tool/log/store/referrer

Afterwards, log in to your Moodle site as an admin and go to _Site administration >
Notifications_ to complete the installation.

Alternatively, you can run

    $ php admin/cli/upgrade.php

to complete the installation from the command line.

## Support
Please use the moodle coummunity forums for help with this plugin:
https://moodle.org/mod/forum/view.php?id=44

Alternatively commercial-level support is available from Catalyst IT:
https://www.catalyst.net.nz/

## Credits
Moodle 4.0 release developed with funding thanks to Canterbury University

![UC-Logo-3-2_3654775638524282877 (1)](https://user-images.githubusercontent.com/362798/202991887-815a122e-5b1b-49f0-8546-0fed94239753.jpg)

Logstore standard, which this plugin builds on top of, was developed by Petr Skoda, Eloy Lafuente and Frédéric Massart.

## License

2023 Catalyst IT

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <https://www.gnu.org/licenses/>.