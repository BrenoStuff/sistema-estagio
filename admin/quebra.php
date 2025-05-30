<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>💥</title>
<style>
body {
  margin: 0;
  background-color: white;
  overflow: hidden;
}

canvas#canvasEfeito {
  position: fixed;
  top: 0;
  left: 0;
  pointer-events: none;
  z-index: 9999;
}

#mensagem {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-family: sans-serif;
  text-align: center;
}

</style>
</head>
<body>

<div id="mensagem">
  <h1>Prepare-se...</h1>
  <p>A tela vai quebrar!</p>
</div>

<audio id="glassSound" src="https://cdn.pixabay.com/download/audio/2022/03/15/audio_70a78b1129.mp3?filename=glass-breaking-6007.mp3"></audio>

<canvas id="canvasEfeito"></canvas>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script>
window.onload = () => {
  quebrarTela();
  
  // ⏳ Após 10 segundos, volta para index.php
  setTimeout(() => {
    window.location.href = "index.php";
  }, 10000);
};

function quebrarTela() {
  const canvas = document.getElementById('canvasEfeito');
  const ctx = canvas.getContext('2d');
  const width = window.innerWidth;
  const height = window.innerHeight;
  canvas.width = width;
  canvas.height = height;

  document.getElementById('glassSound').play();

  html2canvas(document.body).then(screenshot => {
    const img = new Image();
    img.src = screenshot.toDataURL();
    img.onload = () => {
      document.body.style.backgroundColor = "black";
      document.getElementById('mensagem').style.display = 'none';

      ctx.drawImage(img, 0, 0, width, height);
      desenharRachaduras(ctx, width, height);

      setTimeout(() => {
        const shards = gerarPedaços(25, width, height);
        shards.forEach(shard => {
          shard.img = img;
        });

        const interval = setInterval(() => {
          ctx.clearRect(0, 0, width, height);
          ctx.fillStyle = 'black';
          ctx.fillRect(0, 0, width, height);

          shards.forEach(shard => {
            shard.x += shard.vx;
            shard.y += shard.vy;
            shard.vy += 0.8;
            shard.rotation += shard.vr;

            ctx.save();
            ctx.translate(shard.x, shard.y);
            ctx.rotate(shard.rotation);
            ctx.beginPath();
            ctx.moveTo(shard.points[0].x, shard.points[0].y);
            for (let i = 1; i < shard.points.length; i++) {
              ctx.lineTo(shard.points[i].x, shard.points[i].y);
            }
            ctx.closePath();
            ctx.clip();

            ctx.globalAlpha = 0.95;
            ctx.drawImage(
              shard.img,
              0, 0, width, height,
              -shard.x, -shard.y, width, height
            );
            ctx.globalAlpha = 1;

            ctx.restore();

            ctx.save();
            ctx.translate(shard.x, shard.y);
            ctx.rotate(shard.rotation);
            ctx.strokeStyle = "rgba(255,255,255,0.3)";
            ctx.lineWidth = 1;
            ctx.beginPath();
            ctx.moveTo(shard.points[0].x, shard.points[0].y);
            for (let i = 1; i < shard.points.length; i++) {
              ctx.lineTo(shard.points[i].x, shard.points[i].y);
            }
            ctx.closePath();
            ctx.stroke();
            ctx.restore();
          });

          const todosFora = shards.every(s => s.y - 200 > height);
          if (todosFora) {
            clearInterval(interval);
            ctx.fillStyle = 'black';
            ctx.fillRect(0, 0, width, height);
          }
        }, 30);
      }, 500);
    };
  });
}

function desenharRachaduras(ctx, width, height) {
  const center = { x: width / 2, y: height / 2 };
  const linhas = 25;
  ctx.strokeStyle = 'rgba(255,255,255,0.4)';
  ctx.lineWidth = 1;

  for (let i = 0; i < linhas; i++) {
    const angle = Math.random() * Math.PI * 2;
    const length = width * (0.4 + Math.random() * 0.6);
    const x = center.x + Math.cos(angle) * length;
    const y = center.y + Math.sin(angle) * length;

    ctx.beginPath();
    ctx.moveTo(center.x, center.y);
    ctx.lineTo(x, y);
    ctx.stroke();
  }
}

function gerarPedaços(qtd, width, height) {
  const shards = [];
  const center = { x: width / 2, y: height / 2 };
  const angleStep = (Math.PI * 2) / qtd;

  for (let i = 0; i < qtd; i++) {
    const angle1 = i * angleStep + (Math.random() * 0.4 - 0.2);
    const angle2 = (i + 1) * angleStep + (Math.random() * 0.4 - 0.2);

    const r1 = width * (0.6 + Math.random() * 0.4);
    const r2 = width * (0.6 + Math.random() * 0.4);

    const p1 = {
      x: center.x + Math.cos(angle1) * r1,
      y: center.y + Math.sin(angle1) * r1
    };
    const p2 = {
      x: center.x + Math.cos(angle2) * r2,
      y: center.y + Math.sin(angle2) * r2
    };

    shards.push({
      x: center.x,
      y: center.y,
      vx: (Math.random() - 0.5) * 30,
      vy: (Math.random() - 0.5) * 30,
      vr: (Math.random() - 0.5) * 0.2,
      rotation: 0,
      points: [
        { x: 0, y: 0 },
        { x: p1.x - center.x, y: p1.y - center.y },
        { x: p2.x - center.x, y: p2.y - center.y }
      ]
    });
  }

  return shards;
}
</script>

</body>
</html>
