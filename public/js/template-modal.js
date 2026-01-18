document.addEventListener('DOMContentLoaded', function(){
  const cards = document.querySelectorAll('.temp-card');
  const modal = document.getElementById('templateConfirmModal');
  const modalImage = document.getElementById('modalImage');
  const modalTitle = document.getElementById('modalTitle');
  const modalCancel = document.getElementById('modalCancel');
  const modalConfirm = document.getElementById('modalConfirm');
  const modalClose = document.getElementById('modalClose');
  let currentHref = null;

  if(!cards || !modal) return;

  cards.forEach(c => {
    c.addEventListener('click', function(e){
      e.preventDefault();
      currentHref = c.getAttribute('data-href');
      const src = c.getAttribute('data-src');
      modalImage.src = src;
      modalTitle.innerText = c.querySelector('.temp-title')?.innerText || 'Preview';
      modal.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
    });
  });

  function closeModal(){
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    currentHref = null;
  }

  modalCancel.addEventListener('click', closeModal);
  modalClose.addEventListener('click', closeModal);
  modal.addEventListener('click', function(e){
    if(e.target.classList.contains('template-modal') || e.target.classList.contains('modal-backdrop')){
      closeModal();
    }
  });

  modalConfirm.addEventListener('click', function(){
    if(currentHref){
      // small animation before navigate
      modalConfirm.innerText = 'Memilih...';
      setTimeout(()=>{
        window.location.href = currentHref;
      }, 250);
    }
  });
});
