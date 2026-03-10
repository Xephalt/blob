import React from 'react'
import BasicModal from './BasicModal'
import { AnnouncementPopup } from '../../core/announcement'

interface Props {
  popup: AnnouncementPopup
  currentIndex: number
  total: number
  onNext: () => void
  onPrev: () => void
  onFinish: () => void
}

export function AnnouncementPopupModal({ popup, currentIndex, total, onNext, onPrev, onFinish }: Props) {
  const isFirst = currentIndex === 0
  const isLast = currentIndex === total - 1

  return (
    <BasicModal open={true} onClose={onFinish} removeButtons={true}>
      <div className="announcement-popup">

        <h2 className="announcement-popup__title text-center">
          Astuces et nouveautés CommsGPT !
        </h2>

        <div className="announcement-popup__content">
          <h3 className="announcement-popup__astuce-title">{popup.title}</h3>
          <div className="announcement-popup__body flex flex-row">

            <div className="announcement-popup__image w-3/5">
              {popup.imageUrl && (
                <img src={popup.imageUrl} alt="" className="w-full h-full object-cover" />
              )}
            </div>

            <div className="announcement-popup__description w-2/5 pl-4">
              <div
                className="announcement-popup__description-content"
                dangerouslySetInnerHTML={{ __html: popup.content }}
              />
            </div>

          </div>
        </div>

        {/* Footer : prev | dots | next */}
        <div className="announcement-popup__footer flex justify-between items-center mt-4">

          <button
            onClick={onPrev}
            disabled={isFirst}
            className={`announcement-popup__nav-btn flex items-center gap-1 ${isFirst ? 'invisible' : ''}`}
          >
            <span className="material-icons">chevron_left</span>
            <span>Précédent</span>
          </button>

          <div className="flex items-center gap-2">
            {Array(total).fill(0).map((_, index) => (
              <span
                key={index}
                className={`
                  rounded-full transition-all duration-200
                  ${index === currentIndex
                    ? 'bg-bnpp-color-green w-3 h-3'
                    : index < currentIndex
                      ? 'bg-bnpp-color-green opacity-40 w-2 h-2'
                      : 'bg-gray-300 w-2 h-2'
                  }
                `}
              />
            ))}
          </div>

          {isLast ? (
            <button onClick={onFinish} className="announcement-popup__nav-btn flex items-center gap-1">
              <span>Terminer</span>
            </button>
          ) : (
            <button onClick={onNext} className="announcement-popup__nav-btn flex items-center gap-1">
              <span>Suivant</span>
              <span className="material-icons">chevron_right</span>
            </button>
          )}

        </div>

      </div>
    </BasicModal>
  )
}
