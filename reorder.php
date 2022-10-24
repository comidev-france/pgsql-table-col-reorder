<?php

    if ($_GET["key"] !== "djozcz045uilpke-90")
    {
        echo "forbidden";
        exit;
    }
    if (empty($_GET["table"]))
    {
        echo "Please add a table get param";
        exit;
    }
    require_once ".config.php";
    global $db;

    if (isset($_GET["save"]))
    {
        $query = $db->prepare("UPDATE pg_attribute SET attnum = - 4200 - attnum where attnum >= 1 and attrelid = (select oid from pg_class WHERE relname=?) ");
        $query->execute([$_GET["table"]]);
        $data = json_decode(base64_decode($_POST["new_order"]));
        foreach($data as $k => $v)
        {
           $query = $db->prepare("UPDATE pg_attribute
	SET attnum = ?
WHERE attrelid = (select oid from pg_class WHERE relname='group') and attname=?");
           $query->execute([intval($k) + 1, $v]);

        }
        echo "OK";
        exit;
    }


    $query = $db->prepare("SELECT * FROM pg_attribute WHERE attrelid=(select oid from pg_class WHERE relname=?) and attnum >= 1;");
    $query->execute([$_GET["table"]]);

?>
<html>

<body>
<!-- latest compiled and minified css -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css"/>
<center>
    <h1>TABLE [<?=$_GET["table"]?>]</h1>
    <button onclick="saveOrder()">save</button>
</center>
<br/>
<br/>
<!-- simple list -->
<ul class="list-group" ondrop="drop(event)" ondragover="allowDrop(event)">

    <?php while ($row = $query->fetch()): ?>
	    <li id="<?=$row["attname"]?>" class="list-group-item" draggable="true" ondragstart="drag(event)">
            <button class="TOP">TOP</button>
            <button class="BOTTOM">BOTTOM</button>
            |
            <?=$row["attname"]?>
        </li>
    <?php endwhile; ?>
</ul>
<script>

    async function saveOrder()
    {
        let items = document.getElementsByClassName("list-group-item");
        let i = 0;
        let output = {};

        while (i < items.length)
        {
            output[i] = items[i].id;
            i += 1;
        }
        console.log(output);

        let str = btoa(JSON.stringify(output));

        let r = (await fetch(window.location.href + "&save=true", {
            method: 'POST',
            headers: new Headers({
             'Content-Type': 'application/x-www-form-urlencoded', // <-- Specifying the Content-Type
            }),
            body: "new_order="+str
        }));
        let rt = await r.text();
        if (rt != "OK")
            alert("ERREUR : " + rt);
        else
            alert("OK");
    }

    function allowDrop(ev) {
        ev.preventDefault();
    }

    function drag(ev) {
        ev.dataTransfer.setData("text", ev.target.id);
    }

    function drop(ev) {
        ev.preventDefault();
        var data = ev.dataTransfer.getData("text");

        thisdiv = ev.target;
        $(document.getElementById(data)).insertBefore(thisdiv);
    }

    let topBtns = document.getElementsByClassName("TOP");
    let i = 0;
    while (i < topBtns.length)
    {
        topBtns[i].onclick = function()
        {
            while (this.parentElement.previousElementSibling)
            {
                this.parentElement.after(this.parentElement.previousElementSibling);
            }
        }
        i += 1;
    }
    let bottomBtns = document.getElementsByClassName("BOTTOM");
    i = 0;
    while (i < bottomBtns.length)
    {
        bottomBtns[i].onclick = function()
        {
            while (this.parentElement.nextElementSibling)
            {
                this.parentElement.before(this.parentElement.nextElementSibling);
            }
        }
        i += 1;
    }
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
</body>
</html>
