//window.postselector.vote_result.forEach(function(d) {
//    console.log(d);
//});
var loaded = false;
var dataset = window.postselector.vote_result;

var width = 1000,
    radius = 140,
    gap = (10 + 2 * radius),
    height = dataset.length * gap + radius;

var color = function (i) {
    if (i == 0)
        return "#ff7f0e";
    else
        return "#1f77b4";
};

var pie = d3.layout.pie()
    .sort(null);
var arcOver = d3.svg.arc()
    .innerRadius(radius - 50)
    .outerRadius(radius - 20);

var arc = d3.svg.arc()
    .innerRadius(radius - 50)
    .outerRadius(radius - 25);

var svg = d3.select("svg")
    .attr("width", width)
    .attr("height", height)
    .attr("viewBox", "0 0 " + width + " " + height)
    //.attr("preserveAspectRatio","xMidYMin slice")
    .classed("postselector", true);

//d3.select("body").style("height", "100%");
//d3.select("html").style("height", "100%");

var results = svg.selectAll("g.result")
    .data(dataset).enter()
    .append("g")
    .classed("result", true)
    .attr("transform", function (d) {
        return "translate(" + svg.attr("width") / 2 + "," + (dataset.indexOf(d) * gap + radius + 10) + ")";
    });
var titles = results
    .append("text")
    .classed("post", true).attr("width", 300).attr("x", -300)
    .attr("text-anchor", "middle")
    .style("fill", "#000000")
    .attr("y", 0)
    .text(function (d) {
        return d.name;
    });

var charts = results
    .selectAll("path")
    .data(function (d) {
        return pie(getData(d));
    })
    .enter().append("g");

charts.append("path")
    .attr("fill", function (d, i) {
        return color(i);
    })
    .on("mouseover", function (d) {
        if (loaded) {
            d3.select(this).transition()
                .duration(200)
                .attr("d", arcOver);
        }
    })
    .on("mouseout", function (d) {
        if (loaded) {
            d3.select(this).transition()
                .duration(200)
                .attr("d", arc);
        }
    });

charts.selectAll("path").transition()
    .duration(1000)
    .attrTween('d', tweenPie)
    .call(checkEndAll, function (d, i) {
        charts.append('text')
            .attr({
                'text-anchor': 'middle',
                'transform': function (d) {
                    return 'translate(' + arc.centroid(d) + ')';
                }
            })
            .attr("dy", ".4em")
            .style("fill", "#000000")
            .text(function (d, i) {
                if (i == 0)
                    return "Yes";
                else
                    return "No";
            });
        loaded = true;
    });



function checkEndAll(transition, callback) {
    var n = 0;
    transition
        .each(function () {
            ++n;
        })
        .each("end", function () {
            if (!--n) callback.apply(this, arguments);
        });
}


function tweenPie(finish) {
    var start = {
        startAngle: 0,
        endAngle: 0
    };
    var i = d3.interpolate(start, finish);
    return function (d) {
        return arc(i(d));
    };
}

function getData(data) {
    //return [data.yes, data.no];
    //return [10,3]
    if (data.yes == 0 && data.no == 0)
        return [1, 1];
    else
        return [data.yes, data.no];
  }

function getTitle(d) {
    return d.name;
}

$('input[type=submit]').on('click', function (ev) {
    ev.preventDefault();
    var id = ev.currentTarget.id;
    var ix = id.indexOf('-');
    id = id.substring(ix + 1);
    //console.log("submit " + id);
    //console.log(window.postselector.ids[0]);
    $.ajax(window.postselector.ajaxurl, {
        data: {action: "vote_save", security: window.postselector.nonce, id: id},
        dataType: 'text',
        type: 'POST',
        error: function (xhr, status, thrown) {
            console.log("save error: " + status);
            alert("Sorry, could not save data to wordpress");
            $('#' + id).prop('disabled', false);
        },
        success: function (res) {
            //console.log("save result for " + id + ": " + res);
            if (res == '0' || res == '1' || res.substring(0, 1) == '#') {
                alert("res");
            }
            //$('#' + id).prop('disabled', false);
            window.location.reload();
        }
    });

});