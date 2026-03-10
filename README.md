import React from 'react';
import BasicModal from './BasicModal';
import { AnnouncementPopup } from '../../core/announcement';

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

export function AnnouncementPopupModal({
  popup,
  onClose,
  current,
  total,
  onNext,
  onPrev,
  onFinish,
  viewed
}: Props) {
  const isFirst = current === 1
  const isLast = current === total

  return (
    <BasicModal open={true} onClose={onClose} removeButtons={true} setOpen={onFinish}>
      <div className="announcement-popup">
        <h2 className="announcement-popup__title text-center">
          Astuces et nouveautés CommsGPT
        </h2>

        <div className="announcement-popup__content">
          <h3 className="announcement-popup__astuce-title">{popup.title}</h3>

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
                className="announcement-popup__description-content"
                dangerouslySetInnerHTML={{ __html: popup.content }}
              />
            </div>
          </div>
        </div>

        <div className="announcement-popup__footer flex justify-between items-center">
          {/* Précédent - invisible au premier slide pour garder l'alignement */}
          <button
            onClick={onPrev}
            className="btn btn--primary"
            style={{ visibility: isFirst ? 'hidden' : 'visible' }}
          >
            <span className="material-icons">chevron_left</span>
          </button>

          {/* Dots */}
          <div className="announcement-popup__dots flex items-center">
            {Array(total).fill(0).map((_, index) => {
              const dotNum = index + 1
              const isDone = viewed.includes(index) || dotNum < current
              const isCurrent = dotNum === current
              return (
                <span
                  key={index}
                  className="mx-1 rounded-full"
                  style={{
                    display: 'inline-block',
                    width: isCurrent ? '10px' : '8px',
                    height: isCurrent ? '10px' : '8px',
                    backgroundColor: isCurrent ? '#00a862' : isDone ? '#86efac' : '#d1d5db',
                    transition: 'all 0.2s',
                  }}
                />
              )
            })}
          </div>

          {/* Suivant ou Terminer */}
          {isLast ? (
            <button onClick={onFinish} className="btn btn--primary">
              Terminer
            </button>
          ) : (
            <button onClick={onNext} className="btn btn--primary">
              <span className="material-icons">chevron_right</span>
            </button>
          )}
        </div>
      </div>
    </BasicModal>
  );
}