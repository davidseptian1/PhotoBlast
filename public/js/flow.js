document.addEventListener('DOMContentLoaded', function(){
  const copyBtn = document.getElementById('copyBtn');
  const codeBox = document.getElementById('codeBox');
  if(copyBtn && codeBox){
    copyBtn.addEventListener('click', function(){
      const text = codeBox.innerText.trim();
      navigator.clipboard.writeText(text).then(()=>{
        copyBtn.innerText = 'Tersalin';
        setTimeout(()=> copyBtn.innerText = 'Salin Kode', 2000);
      }).catch(()=>{
        copyBtn.innerText = 'Gagal';
      });
    });
  }
});
