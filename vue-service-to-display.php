<?php 
  // Configure database connector
  define( 'DB_HOST', 'localhost' );
  define( 'DB_USER', 'root' );
  define( 'DB_PWD', 'root' );
  define( 'DB_NAME', 'test' );  

/////////////

  // Connect to database
  $Link = mysqli_connect(DB_HOST, DB_USER, DB_PWD, DB_NAME);
  if ( !$Link ) {
    echo "Under Maintenance";
    exit();  
  }



  //RETURN ARRAY OF RECORDS
  function getRecords() {
    Global $Link;
    $sql = "SELECT id, url, site FROM `test`";
  $results = mysqli_query($Link, $sql );

    if (!$results) { echo mysqli_error($Link); echo ' <br>' .  $sql; }
    
    $rowsAll = [];
      while ($row = $results->fetch_assoc()) {
          $rowsAll[] = $row;
    }
    return $rowsAll;
  }

// IF AJAX POST - SEND RECORDS
if ($_POST['get_records']) {
    $allRecords = getRecords();
    echo json_encode($allRecords);
    die();
}

  // THEN A THE END OF THE FILE
  mysqli_close($Link);
?>



<html>
	<head>
		<title> Service to Display </title>
		<style>

		</style>
    <!-- PRODUCTION vue.js framework 
		<script src="https://cdn.jsdelivr.net/npm/vue"></script>-->
        <!-- DEV VUE -->
    <script src="https://unpkg.com/vue@2.5.13/dist/vue.js"></script>
    <!-- include the jquery for ajax calls -->
    <script src="https://code.jquery.com/jquery-3.2.1.js"></script>
      <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/css/bootstrap.min.css" integrity="sha384-Zug+QiDoJOrZ5t4lssLdxGhVrurbmBWopoEl+M6BdEfwnCJZtKxi1KgxUyJq13dy" crossorigin="anonymous">
	</head>
	<body>

 <div id="app"> 
  <!-- TEMPLATE CONTAINER -->
  <div class="container">
    <component :is="currentView" :posts="thePosts" transition="fade" transition-mode="out-in"></component>
  </div>
</div>

<template id="manage-template">
  <div>
  <h3>Sites
  </h3> 
    <table class="table">
      <tr>
          <th>ID</th>
          <th>Url </th>
          <th> Site</th>
      </tr>
      <tr  v-for="post in posts"> 
        <td>
         {{ post.id }}
        </td>
        <td>
           <a v-bind:href="post.url" target="_blank"> {{ post.url }} </a>
        </td>
        <td>
          {{ post.site }}
        </td>
      </tr>
   </table>

  </div>
</template>



<script>
 Vue.component('manage-posts', {
    template: '#manage-template',
    props: ['posts'],
  })

  var vm = new Vue({
    el: '#app',
    mounted: function() { 
      this.getRecords()
    },
    data: {
      currentView: 'manage-posts',
      thePosts: ''
    },
    methods: {
      getRecords: function() {
        jQuery.ajax({
              method: "POST",
              url: '',
              data: {'get_records': true},
              dataType: "JSON"
            }).done(function(resp){
              vm.thePosts = resp;
            });
      } // end getRecords
    }// end methods
  })
</script>


	</body>
</html>