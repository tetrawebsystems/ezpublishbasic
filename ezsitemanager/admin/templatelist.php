<?php
//
// $Id: templatelist.php 9330 2002-03-04 12:56:24Z ce $
//
// Created on: <24-Sep-2001 14:12:07 bf>
//
// This source file is part of eZ publish, publishing software.
//
// Copyright (C) 1999-2001 eZ Systems.  All rights reserved.
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, US
//

include_once( "classes/ezfile.php" );

if ( $filePath == "" )
    $filePath = ".";
else
    $filePath = ".-$filePath";

$realPath = str_replace( "-", "/", $filePath );

$filePathArray = explode( "-", $filePath );

if ( count( $filePathArray ) == 3 )
    $dir = eZFile::dir( $realPath . "/templates/" );
else
    $dir = eZFile::dir( $realPath );

$ini =& INIFile::globalINI();
$Language = $ini->read_var( "eZSiteManagerMain", "Language" );

$t = new eZTemplate( "ezsitemanager/admin/" . $ini->read_var( "eZSiteManagerMain", "AdminTemplateDir" ),
                     "ezsitemanager/admin/" . "/intl", $Language, "templatelist.php" );
$t->setAllStrings();

$t->set_file( "template_list_tpl", "templatelist.tpl" );

$t->set_block( "template_list_tpl", "file_item_tpl", "file_item" );
$t->set_var( "file_item", "" );

while ( $entry = $dir->read() )
{
    if ( $entry != "." && $entry != ".." )
    {
        if ( count( $filePathArray ) == 1 )
        {
            // top level modules
            if ( preg_match( "#^ez[a-z]+$#", $entry ) )
            {
                $t->set_var( "file_name", $entry  );
                $t->set_var( "file_href", "/sitemanager/template/list/$entry" );
                $t->parse( "file_item", "file_item_tpl", true );
            }
        }
        else if ( count( $filePathArray ) == 2 )
        {
            if ( $entry == "user" || $entry == "admin" )
            {
                // user/admin select
                $t->set_var( "file_name", $entry  );
                $t->set_var( "file_href", "/sitemanager/template/list/" . $filePathArray[1] . "-$entry"  );
                $t->parse( "file_item", "file_item_tpl", true );
            }
        }
        else if ( count( $filePathArray ) == 3 )
        {
            if ( $entry != "CVS" )
            {
                $t->set_var( "file_href", "/sitemanager/template/list/" . $filePathArray[1] . "-" . $filePathArray[2] . "-templates" . "-$entry"  );
                $t->set_var( "file_name", $entry  );

                $t->parse( "file_item", "file_item_tpl", true );
            }
        }
        else if ( count( $filePathArray ) == 5 )
        {
            if ( preg_match( "#[a-z]+\.tpl$#", $entry )
                 )
            {
                $t->set_var( "file_href", "/sitemanager/template/edit/" . $filePathArray[1] . "-" . $filePathArray[2] . "-templates" . "-" . $filePathArray[4] . "-$entry"  );
                $t->set_var( "file_name", $entry  );

                $t->parse( "file_item", "file_item_tpl", true );
            }
        }
    }
}

$t->pparse( "output", "template_list_tpl" );
?>
