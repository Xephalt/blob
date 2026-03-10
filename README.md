import React from ‘react’;
import BasicModal from ‘./BasicModal’
import { AnnouncementPopup } from ‘../../core/announcement’

interface Props {
popup: AnnouncementPopup
onClose: () => void
current: number
total: number
onNext: () => void
onPrev: () => void
onFinish: () => void
viewed: number[]
}

export function AnnouncementPopupModal({ popup, onClose, current, total, onNext, onPrev, onFinish, viewed }: Props) {
return (
<BasicModal open={true} onClose={onClose} removeButtons={true} setOpen={onFinish}>
<div className="announcement-popup">
<h2 className="announcement-popup__title text-center">Astuces et nouveautés CommsGPT</h2>
<div className="announcement-popup__content">
{/* H3 pleine largeur AVANT le body flex */}
<h3 className=“announcement-popup__astuce-title” style={{ width: ‘100%’ }}>{popup.title}</h3>
<div className="announcement-popup__body flex flex-row">
<div className="announcement-popup__image w-3/5">
{popup.imageUrl && (
<img
src={popup.imageUrl}
alt=""
className="w-full h-full object-cover"
/>
)}
</div>
<div className="announcement-popup__description w-2/5 pl-4">
{/* HTML depuis CKEditor — dangerouslySetInnerHTML intentionnel */}
<div
className=“announcement-popup__description-content”
dangerouslySetInnerHTML={{ __html: popup.content }}
/>
</div>
</div>
</div>
<div className="announcement-popup__footer flex justify-between items-center">
<button
onClick={onPrev}
className={`btn btn--primary ${current === 1 ? 'hidden' : ''}`}
disabled={current === 1}
>
<span className="material-icons">chevron_left</span>
Précédent
</button>
<div className="announcement-popup__dots flex">
{Array(total).fill(0).map((_, index) => (
<span
key={index}
className={`announcement-popup__dot w-2 h-2 rounded-full ${index < current ? 'bg-bnpp-color-green' : 'bg-gray-500'} mx-1`}
/>
))}
</div>
<button
onClick={current === total ? onFinish : onNext}
className={`btn btn--primary ${current === total ? '' : ''}`}
disabled={current === total && false}
>
{current === total ? ‘Terminer’ : `Astuce suivante (${current}/${total})`}
{current !== total && <span className="material-icons">chevron_right</span>}
</button>
</div>
</div>
</BasicModal>
)
}