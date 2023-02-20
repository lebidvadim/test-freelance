<?php include 'Controller.php'?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport"
              content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Test-freelance</title>
        <link rel="stylesheet" href="public/css/bootstrap.min.css">
    </head>
    <body class="bg-white">
        <div class="container pt-5 mb-5">
            <div>
                <?php foreach ($app->getSkills() as $skills):?>
                    <a class="btn btn-outline-primary btn-sm mb-2<?php if(isset($_GET['skill']) && $_GET['skill'] == $skills['id']):?> active<?php endif;?>" href="/?skill=<?php echo $skills['id']?>"><?php echo $skills['name']?></a>
                <?php endforeach;?>
            </div>
            <?php $projects = $app->getProjects()?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Назва проєкту</th>
                        <th>Бюджет</th>
                        <th>Данні замовника</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($projects['projects'] as $project):?>
                    <tr>
                      <td><a href="<?php echo $project['link']?>" target="_blank"><?php echo $project['name']?></a></td>
                      <td><?php echo $project['amount']?> грн.</td>
                      <td><?php echo $project['last_name']?> <?php echo $project['first_name']?> (<?php echo $project['login']?>)</td>
                    </tr>
                <?php endforeach;?>
                </tbody>
            </table>
            <?php if($projects['pie_chart']['500'] > 0 || $projects['pie_chart']['1000'] || $projects['pie_chart']['5000'] || $projects['pie_chart']['>']):?>
            <div style="height: 500px;display: flex; justify-content: center">
              <canvas id="myChart"></canvas>
            </div>
            <?php endif;?>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script type="application/javascript">
            const ctx = document.getElementById('myChart');
            new Chart(ctx, {
            type: 'doughnut',
            data: {
              labels: [
                 '500',
                 '500-1000',
                 '1000-5000',
                 'больше 5000',
              ],
              datasets: [{
                label: 'My First Dataset',
                data: [<?php echo $projects['pie_chart']['500']?>, <?php echo $projects['pie_chart']['1000']?>, <?php echo $projects['pie_chart']['5000']?>,<?php echo $projects['pie_chart']['>']?>],
                backgroundColor: [
                  'rgb(255, 99, 132)',
                  'rgb(54, 162, 235)',
                  'rgb(255, 205, 86)',
                  'rgb(255, 0, 0)'
                ],
                hoverOffset: 2
              }]
            },

          });
        </script>
    </body>
</html>