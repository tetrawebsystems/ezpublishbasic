<?php
// 
// $Id: unpublishedlist.php 6617 2001-08-24 12:38:08Z th $
//
// Created on: <18-Oct-2000 14:41:37 bf>
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

include_once( "classes/INIFile.php" );
include_once( "classes/eztemplate.php" );
include_once( "classes/ezlocale.php" );

include_once( "ezarticle/classes/ezarticlecategory.php" );
include_once( "ezarticle/classes/ezarticle.php" );
include_once( "ezarticle/classes/ezarticletool.php" );
include_once( "ezuser/classes/ezobjectpermission.php" );
include_once( "classes/ezcachefile.php" );
include_once( "classes/ezlist.php" );

$ini =& INIFile::globalINI();

$Language = $ini->read_var( "eZArticleMain", "Language" );
$Locale = new eZLocale( $Language );
$AdminListLimit = $ini->read_var( "eZArticleMain", "AdminListLimit" );
$languageIni = new INIFIle( "ezarticle/admin/intl/" . $Language . "/unpublishedlist.php.ini", false );



if( isset( $DeleteArticles ) )
{
    if ( count ( $ArticleArrayID ) != 0 )
    {
        foreach( $ArticleArrayID as $TArticleID )
        {
            if( eZObjectPermission::hasPermission( $TArticleID, "article_article", 'w' )
                || eZArticle::isAuthor( eZUser::currentUser(), $TArticleID ) )
            {
                $article = new eZArticle( $TArticleID );

                // get the category to redirect to
                $articleID = $article->id();

                $categoryArray = $article->categories();
                $categoryIDArray = array();
                foreach ( $categoryArray as $cat )
                {
                    $categoryIDArray[] = $cat->id();
                }
                $categoryID = $article->categoryDefinition();
                $categoryID = $categoryID->id();

                // clear the cache files.
                eZArticleTool::deleteCache( $TArticleID, $categoryID, $categoryIDArray );
                $article->delete();
            }
        }
    }
}

$t = new eZTemplate( "ezarticle/admin/" . $ini->read_var( "eZArticleMain", "AdminTemplateDir" ),
                     "ezarticle/admin/intl/", $Language, "unpublishedlist.php" );

$t->setAllStrings();

$t->set_file( array(
    "unpublished_list_page_tpl" => "unpublishedlist.tpl"
    ) );


// article
$t->set_block( "unpublished_list_page_tpl", "article_list_tpl", "article_list" );
$t->set_block( "article_list_tpl", "article_item_tpl", "article_item" );

$t->set_block( "article_item_tpl", "article_is_published_tpl", "article_is_published" );
$t->set_block( "article_item_tpl", "article_not_published_tpl", "article_not_published" );
$t->set_block( "article_item_tpl", "article_edit_tpl", "article_edit" );


// prev/next
$t->set_block( "unpublished_list_page_tpl", "previous_tpl", "previous" );
$t->set_block( "unpublished_list_page_tpl", "next_tpl", "next" );

$t->set_var( "site_style", $SiteDesign );


// set the offset/limit
if ( !isset( $Offset ) )
    $Offset = 0;

if ( !isset( $Limit ) )
    $Limit = $AdminListLimit;

// articles
$article = new eZArticle();

$articleList =& $article->articles( "time", "only",  $Offset, $Limit );
$articleCount = $article->articleCount( "only" );

$i=0;
$t->set_var( "article_list", "" );


foreach ( $articleList as $article )
{
    if( eZObjectPermission::hasPermission( $article->id(), "article_article", 'r') ||
        eZArticle::isAuthor( eZUser::currentUser(), $article->id() ) )
    {
        if ( $article->name() == "" )
            $t->set_var( "article_name", "&nbsp;" );
        else
            $t->set_var( "article_name", $article->name() );

        $t->set_var( "article_id", $article->id() );

        if ( $article->isPublished() == true )
        {
            $t->parse( "article_is_published", "article_is_published_tpl" );
            $t->set_var( "article_not_published", "" );        
        }
        else
        {
            $t->set_var( "article_is_published", "" );
            $t->parse( "article_not_published", "article_not_published_tpl" );
        }

        if ( ( $i % 2 ) == 0 )
        {
            $t->set_var( "td_class", "bglight" );
        }
        else
        {
            $t->set_var( "td_class", "bgdark" );
        }

        
        if( eZObjectPermission::hasPermission( $article->id(), "article_article", 'w') ||
            eZArticle::isAuthor( eZUser::currentUser(), $article->id() ) )
        {
            $t->parse( "article_edit", "article_edit_tpl", false );
        }
        else
            $t->set_var( "article_edit", "" );


        $t->parse( "article_item", "article_item_tpl", true );
        $i++;
    }
}

eZList::drawNavigator( $t, $articleCount, $AdminListLimit, $Offset, "unpublished_list_page_tpl" );


if ( count( $articleList ) > 0 )    
    $t->parse( "article_list", "article_list_tpl" );
else
    $t->set_var( "article_list", "" );


$t->pparse( "output", "unpublished_list_page_tpl" );


?>