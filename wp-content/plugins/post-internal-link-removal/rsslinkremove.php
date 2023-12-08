<?php
/*
Plugin Name: Post Internal Link Removal
Plugin URI: http://www.rsoftsolution.com/wordpress-plugin-development.html
Description: By using this plugin all link can be removed with your link vary easily.In pro version you can get more features.
Author: R Software Solution
Author URI: http://www.rsoftsolution.com/
Version: 3.1
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/




if ( ! defined( 'ABSPATH' ) )exit;


function linkremoved_install(){
	global $wpdb; 	
	$rss_linkremoved = $wpdb->prefix ."linkremoved";
	$exist_table = $wpdb->get_var("show tables like '$rss_linkremoved'");
	
	
	
	if($exist_table != $rss_linkremoved){
			
    $sql = "CREATE TABLE `" . $rss_linkremoved . "`  (                            
                `id` bigint(20) NOT NULL auto_increment PRIMARY KEY,               
                `userid` bigint(20) NOT NULL,                          
                `urlupdate` varchar(255)  NULL,                     
                `updatelastID` text  NULL,                      
                `totalupdate` bigint(20)  NULL,                  
                `updatedate` varchar(200)  NULL                                            
              );";
				
		$result = $wpdb->query($sql);
	}	
}
function linkremoved_deactivate(){
	global $wpdb; 
	$rss_linkremoved = $wpdb->prefix ."linkremoved";
	$exist_table = $wpdb->get_var("show tables like '$rss_linkremoved'");
	
	 if($exist_table == $rss_linkremoved){
			$sqldrop="drop table ".$rss_linkremoved;
			$result = $wpdb->query($sqldrop);
	 	}
	
	}	

function linkremoveform(){
	$userinfo='';
	
	if (isset( $_POST['_wpnonce'] ) and !wp_verify_nonce( $_POST['_wpnonce']) ) {

		   print 'Sorry, your nonce did not verify.';
		   exit;

		} else {
	
	
			 if(!current_user_can('administrator') ) {  
					
					exit;
				 } 

		   if(isset($_POST['btnremove']) and $_POST['btnremove']=="Remove"){
			global $wpdb;
			$rss_table = $wpdb->prefix . "posts"; 	
			$rss_linkremoved = $wpdb->prefix ."linkremoved";
			$strartpt=sanitize_text_field($_POST['startpt']);
			$endpt=sanitize_text_field($_POST['endpt']);
			$PID=sanitize_text_field($_POST['PID']);
			$skey=sanitize_text_field($_POST['skey']);
			$destkey=sanitize_text_field($_POST['destlink']);
			$follow=sanitize_text_field($_POST['followopt']);
			
			if(!empty($PID)){
				
				if(!intval($PID)){
						  echo "<br><p>Invalid post ID.This should be in number.</p>";
						 exit;
					}
				
			}else{
				
					if(!intval($strartpt)){
					 echo "<br><p>Invalid starting post entry.This should be in number.</p>";
					 exit;
						}
					if(!intval($endpt)){
						  echo "<br><p>Invalid end post entry.This should be in number.</p>";
						 exit;
					}
				
			}
			
			
			
			
			if(empty($destkey)){
				  echo "<br><p>Destination field can not be empty.</p>";
				 exit;
			}
			if($PID!='' and $PID>0){
			$sql="select ID,post_content from $rss_table where post_status='publish' and post_type='post' and ID=$PID";			
			}else{
			$sql="select ID,post_content from $rss_table where post_status='publish' and post_type='post' order by ID desc limit $strartpt,$endpt";
			}
			$result= $wpdb->get_results($sql);
			$rowupid='';
			$countup=0;
		foreach($result as $row){
			
			$updatedid=$wpdb->get_var("select updatelastID from $rss_linkremoved");
			
			if(preg_match("/$row->ID/",$updatedid) and empty($PID)){
				
				
					   continue;
				
				
				
			}else{
				
				
		
				$str=$row->post_content;
				
				
				preg_match_all("|<([^>]+)>(.*)|U",$str,$out);
				$newstr='';
				
				
				if(!empty($skey)){
					
					if(!empty($follow)){
						$followstr=' rel="'.$follow.'"';
					}else{
						$followstr='';
					}
					
				  $str=str_replace($skey,'<a href="'.$destkey.'"'.$followstr.'>'.$skey.'</a>' ,$str);
				  $countup=($countup+1); 
				}else{
					
					
				for($cnt=0;$cnt<count($out[0]);$cnt++){
				
				if(preg_match('/<a /',$out[0][$cnt],$matches)){
				
				    if(!empty($follow)){
						$followstr=' rel="'.$follow.'"';
					}else{
						$followstr='';
					}
				
					$str=str_replace($out[0][$cnt],'<a href="'.$destkey.'"'.$followstr.'>' ,$str);
				  
				 $countup=($countup+1);
				 continue;
				 
				 }
				//$newstr.=$out[0][$cnt];		
				}
					
				}
				
				
				
				
				$newstr=addslashes($str);
				$wpdb->query("update $rss_table set post_content='".$newstr."' where ID=".$row->ID);
				
				$rowupid.=$row->ID.',';
		
				
			}
			
			
		
		
		}
		
		if(!empty($rowupid))
				{
					$totalupdate=$countup;
					$wpdb->query("insert into $rss_linkremoved(userid,urlupdate,updatelastID,totalupdate,updatedate) values (1,'".$destkey."','".$rowupid."',".$totalupdate.",'".date('Y-m-d')."');");	
				}
		
			}

}

 	
	global $wpdb; 
	$rss_linkremoved = $wpdb->prefix ."linkremoved";
	$sqlquery="select count(*) as c from ".$rss_linkremoved;
	$rowcnt=$wpdb->get_var($sqlquery);
	
	if( $rowcnt>0){
		$arrid=array();
		$sqlquery="select * from ".$rss_linkremoved;
	    $res=$wpdb->get_results($sqlquery);
		foreach($res as $showrow){
			 $newupdatestr=substr($showrow->updatelastID,0,-1);		
			 $arrid=@explode(',',$newupdatestr);
			$cntlist+=count($arrid);
		}
		echo "<br><h3>".($cntlist).' post has used to remove the internal link.</h3>';
	}else{
		echo "<br><h3>There is a no link has been removed from post till now.</h3>";
	}
	
	?>
 <h1>Post Internal Link Removal</h1>
<table width="80%"><tr><td width="50%" align="center"><a href="admin.php?page=linkremoveform&module=remove" style="font-size:18px;">Remove Post Link</a></td><td width="50%" align="center"><a href="admin.php?page=linkremoveform&module=specficpost" style="font-size:18px;">Specific Post</a></td></tr></table>


<table width="80%" cellpadding="0" cellspacing="20">

  <tr>
    <td valign="top" width="60%" align="left">
    <form method="post" action="">
        <table width="100%" cellpadding="0" cellspacing="0">
      <?php $module=sanitize_text_field($_GET['module']);?>
        <?php if($module=="remove" or empty($module)){?>
        
          <tr>
            <td valign="top"><p>Starting Post : </p><p style="font-size:12px;">(From where you want to start e.g 1 or 2 or 3 or 4 5 ....so on)</p></td>
            <td valign="top"><input type="text" name="startpt" id="startpt" /></td>
          </tr>
          <tr>
            <td valign="top"><p>Ending Post : </p><p style="font-size:12px;">(How many post you want to process e.g. 10 or 20 or 30 etc.)</p></td>
            <td valign="top"><p>
                <input type="text" name="endpt" id="endpt" />
              </p></td>
          </tr>
          
        <?php  } ?>
        
         <?php if($module=="specficpost"){?>
         <tr><td colspan="2">Here you can enter specific post id from which you want to remove link. You can also mention searching word from where you want to remove the link</td></tr>
          <tr>
            <td valign="top"><p>Post ID : </p></td>
            <td><p>
                <input type="text" name="PID" id="PID" />
              </p></td>
          </tr>
          <tr>
            <td valign="top"><p>Search Keyword :</p></td>
            <td><input type="text" name="skey" id="skey" /></td>
          </tr>
        <?php } ?>  
          <tr>
            <td valign="top"><p>Destination Link : </p><p style="font-size:12px;">(Destination link should be like this http://www.yoursite.com)</p></td>
            <td valign="top"><input type="text" name="destlink" id="destlink" />
              </p></td>
          </tr>
          <tr>
            <td valign="top"><p>Link Follow Option : </p><p style="font-size:12px;">(rel="follow" or rel="nofollow" will be added according to your choice)</p></td>
            <td valign="top">Follow<input type="radio" name="followopt" id="followopt1" value="follow" />Nofollow<input type="radio" name="followopt" id="followopt2" value="nofollow" checked="checked" />
              </p></td>
          </tr>
           <tr>
           <td></td>
            <td valign="top" align="center"><p><?php wp_nonce_field(); ?>
                <input type="submit" name="btnremove" id="btnremove" value="Remove" />
              </p></td>
          </tr>
        </table>
        
      </form>
    </td>
    <td valign="top"><h2>You can also buy pro version of this plugin which has extra features.</h2>
      <h3>Pro Features :</h3>
      <p>Internal post link  will be auto replaced with your link.</p>
      <p>You will be able to see all post detail which link has been replaced.</p>
      <p><input type="button" style="width:120px; background-color:red;color:white; height:50px; border:1px red solid; cursor:pointer;" value="Buy Now" onclick="javascript:document.location.href='https://rsoftsolution.com/Post-Internal-Link-Removal-Plugin.html';"/></p>
      </td>
  </tr>
</table>
<?php

}

function linkremoved_add_menu(){
	add_menu_page('Link Remove', 'Link Remove', 'manage_options', 'linkremoveform', 'linkremoveform');	
}

register_activation_hook( __FILE__, 'linkremoved_install');
register_deactivation_hook( __FILE__, 'linkremoved_deactivate');
add_action('admin_menu', 'linkremoved_add_menu');

