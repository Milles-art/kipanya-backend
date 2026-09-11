(() => {
  const page = document.querySelector('[data-wear-product-page]'); if(!page) return;
  const dataEl = page.querySelector('[data-wear-product-data]');
  let product; try { product = JSON.parse(dataEl.textContent); } catch { return; }
  const variants = product.variants || [];
  const sizeWrap = page.querySelector('[data-wear-size-options]');
  const colorBlock = page.querySelector('[data-wear-color-block]');
  const colorWrap = page.querySelector('[data-wear-color-options]');
  const addBtn = page.querySelector('[data-wear-add-to-cart]');
  const qtyEl = page.querySelector('[data-wear-qty]'); let qty = 1; let selectedSize = null; let selectedColor = null;
  const sizes = [...new Set(variants.map(v => v.size).filter(Boolean))];
  const colorsFor = size => [...new Set(variants.filter(v => v.size === size && v.in_stock).map(v => v.color).filter(Boolean))];
  const current = () => variants.find(v => v.in_stock && v.size === selectedSize && (!selectedColor || v.color === selectedColor));
  function renderSizes(){ sizeWrap.innerHTML=''; sizes.forEach(size=>{const v=variants.find(x=>x.size===size);const b=document.createElement('button');b.type='button';b.textContent=size;b.disabled=!(v?.in_stock);b.className=selectedSize===size?'is-selected':'';b.setAttribute('aria-pressed', String(selectedSize===size));b.addEventListener('click',()=>{selectedSize=size; const cs=colorsFor(size); selectedColor=cs.length===1?cs[0]:null; renderSizes(); renderColors(); paint();}); sizeWrap.appendChild(b);}); }
  function renderColors(){const cs=colorsFor(selectedSize); colorBlock.hidden=cs.length<=1; colorWrap.innerHTML=''; if(cs.length<=1){selectedColor=cs[0]||null;return;} cs.forEach(color=>{const b=document.createElement('button');b.type='button';b.textContent=color;b.className=selectedColor===color?'is-selected':'';b.addEventListener('click',()=>{selectedColor=color;renderColors();paint();});colorWrap.appendChild(b);});}
  function paint(){const v=current();addBtn.disabled=!v;addBtn.textContent=v?'Add to bag':'Select a size';qtyEl.textContent=qty;}
  page.querySelector('[data-wear-qty-inc]')?.addEventListener('click',()=>{qty=Math.min(20,qty+1);paint()});page.querySelector('[data-wear-qty-dec]')?.addEventListener('click',()=>{qty=Math.max(1,qty-1);paint()});
  addBtn?.addEventListener('click',async()=>{const v=current();if(!v||!window.KipanyaCart)return;addBtn.disabled=true;addBtn.textContent='Adding…';const res=await window.KipanyaCart.addItem(Number(v.id),qty);if(res.ok){addBtn.textContent='Added to bag';}else{addBtn.textContent='Could not add';}setTimeout(paint,1000)});
  renderSizes();renderColors();paint();
})();
