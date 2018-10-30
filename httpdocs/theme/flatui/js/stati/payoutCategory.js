!(function (d3) {

            var parseTime = d3.timeParse("%Y%m");
                       
                d3.json("/backend/index/getpayoutcategory?catid="+window.selectedCatid, function(error, data) {                                                        
                if (error) throw error;
                data = data.results;   

                console.log(data);

                if(!data){
                     $("#payoutCategoryLineChart"+window.selectedCatid).text('no data found!');
                    return;
                }

                if(window.selectedCatid==0)
                {
                    //$pids = array(152, 233,158, 148,491,445,295);
                    data.forEach(function (d) {                   
                       d.year = parseTime(d.yearmonth);
                       d.variableA = +d.amount;                                    
                       d.variable152 = +d.amount152; 
                       d.variable233 = +d.amount233; 
                       d.variable158 = +d.amount158; 
                       d.variable404 = +d.amount404; 
                       d.variable148 = +d.amount148;
                       d.variable491 = +d.amount491;
                       d.variable445 = +d.amount445;
                       d.variable295 = +d.amount295;
                    });

                    var chart = makeLineChart(data, 'year', {
                        [window.selectedCatTitle]: {column: 'variableA'},
                        'App addons': {column: 'variable152'},
                        'Apps': {column: 'variable233'},
                        'Art (Images/Drawings/Illustrations)': {column: 'variable158'},
                        'Distros': {column: 'variable404'},
                        'Linux/Unix Desktops': {column: 'variable148'},
                        'Phone/Mobile': {column: 'variable491'},
                        'UI Concepts': {column: 'variable445'},
                        'Wallpapers': {column: 'variable295'}
                    }, {xAxis: 'Month', yAxis: 'Amount'});

                }else{
                        data.forEach(function (d) {                   
                       d.year = parseTime(d.yearmonth);
                       d.variableA = +d.amount;                                    
                    });

                    var chart = makeLineChart(data, 'year', {
                        [window.selectedCatTitle]: {column: 'variableA'}
                    }, {xAxis: 'Month', yAxis: 'Amount'});
                }
                
                chart.bind("#payoutCategoryLineChart"+window.selectedCatid);
                chart.render();

            });


})(d3);