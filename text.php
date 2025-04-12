<?php

$a = '{"values" : [0,0],"curretID": 0}';
$v = json_decode($a,true);
var_dump($a);
echo $v['values'];


?>




<!DOCTYPE html>
<html>
<head><textarea id="main2" name="w3review" rows="1" cols="1"></textarea>
<div id="main" contenteditable="true"> </div>
<style>

.third-level-menu
{
    position: absolute;
    top: 0;
    right: -150px;
    width: 150px;
    list-style: none;
    padding: 0;
    margin: 0;
    display: none;
}

.third-level-menu > li
{
    height: 30px;
    background: #999999;
}
.third-level-menu > li:hover { background: #CCCCCC; }

.second-level-menu
{
    position: absolute;
    top: 30px;
    left: 0;
    width: 150px;
    list-style: none;
    padding: 0;
    margin: 0;
    display: none;
}

.second-level-menu > li
{
    position: relative;
    height: 30px;
    background: #999999;
}
.second-level-menu > li:hover { background: #CCCCCC; }

.top-level-menu
{
    list-style: none;
    padding: 0;
    margin: 0;
}

.top-level-menu > li
{
    position: relative;
    float: left;
    height: 30px;
    width: 150px;
    background: #999999;
}
.top-level-menu > li:hover { background: #CCCCCC; }

.top-level-menu li:hover > ul
{
    /* On hover, display the next level's menu */
    display: inline;
}


/* Menu Link Styles */

.top-level-menu a /* Apply to all links inside the multi-level menu */
{
    font: bold 14px Arial, Helvetica, sans-serif;
    color: #FFFFFF;
    text-decoration: none;
    padding: 0 0 0 10px;

    /* Make the link cover the entire list item-container */
    display: block;
    line-height: 30px;
}
.top-level-menu a:hover { color: #000000; }
</style>
<ul class="top-level-menu">
    <li><a href="#">About</a></li>
    <li><a href="#">Services</a></li>
    <li>
        <a href="#">Offices</a>
        <ul class="second-level-menu">
            <li><a href="#">Chicago</a></li>
            <li><a href="#">Los Angeles</a></li>
            <li>
                <a href="#">New York</a>
                <ul class="third-level-menu">
                    <li><a href="#">Information</a></li>
                    <li><a href="#">Book a Meeting</a></li>
                    <li><a href="#">Testimonials</a></li>
                    <li><a href="#">Jobs</a></li>
                </ul>
            </li>
            <li><a href="#">Seattle</a></li>
        </ul>
    </li>
    <li><a href="#">Contact</a></li>
</ul>
</body>
</html>
