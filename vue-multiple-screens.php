<html>
	<head>
		<title> Records Test </title>
		<style>

		</style>
    <!-- include the vue.js framework -->
		<!-- <script src="https://cdn.jsdelivr.net/npm/vue"></script> -->
    <!-- DEV VUE -->
      <script src="https://unpkg.com/vue@2.5.13/dist/vue.js"></script>
    <!-- include the jquery for ajax calls -->
    <script
  src="https://code.jquery.com/jquery-3.2.1.js"></script>
	</head>
	<body>

  <div id="app">
      <button class="btn" @click="setView('screen1')">Screen 1</button><button class="btn" @click="setView('screen2')">Screen 2</button>
      <component :is="currentView" :todos="todos" :set-view="setView" :text="text" :params="params"></component>
  </div>

  <template id="screen1">
    <div id="something">
      This is screen1
      <br>
      {{text}}
    </div>
  </template>


  <template id="screen2">
    <div id="todos">
      <ol>
        <li v-for="todo in todos">
          <a href="#" v-on:click="setView('screen3',todo.id)">{{ todo.name }}</a>
        </li>
      </ol>
    </div>
  </template>

  <template id="screen3">
    <div id="something">
      This is screen 3
      <br>
      {{todos[params].name}}<br>
      {{todos[params].comment}}
    </div>
  </template>

<script>

Vue.component('screen1', {
    template: '#screen1',
    props: ['text'],
  })

Vue.component('screen2', {
    template: '#screen2',
    props: ['todos', 'set-view'],
    data: function (){
        return {
        } // end  return 
    } // end data
  })

Vue.component('screen3', {
    template: '#screen3',
    props: ['todos','params'],
    data: function (){
        return {
        } // end  return 
    } // end data
  })


var vm = new Vue({
  el: '#app',
  data: {
    currentView: 'screen1',
    text: 'This is some text',
    params: '',
    todos: [{'id': 0, 'name':'john', 'comment':'johns comment'},{'id': 1, 'name':'chuck', 'comment': 'Chucks comment'}]
  }, 
  methods: {
    setView: function(viewVal,param) {
      vm.currentView = viewVal;
      vm.params = param;
    }
  }

  });

</script>
