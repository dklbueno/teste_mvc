app.controller("MainController", ["$scope", "$http",
    function($scope, $http) {
		$http({
		  method: 'GET',
		  url: path+'usuario/lista'
		}).then(function successCallback(response) {
			console.log(response.data);
			$scope.users = response.data;
		}, function errorCallback(response) {

		});
		
		$scope.Excluir = function(id) { 
		  	if(confirm('Tem certeza que deseja excluir este registro?')){
		  		$http({
		  		  method: 'GET',
		  		  url: path+'usuario/excluir/id/'+id
		  		}).then(function successCallback(response) {
		  			console.log(response.data);
		  			$("#user_"+id).parent().remove();
		  		}, function errorCallback(response) {

		  		});
		  	}
		};
		
		$scope.Editar = function(id) { 
		  	location.href=path+'usuario/cadastrar/editar/'+id;
		};
    }
]);