import React from 'react'
import { useAnnouncementPopups } from '../../hooks/announcements/useAnnouncementPopups'
import { AnnouncementPopupModal } from './AnnouncementPopupModal'

interface Props {
  children: React.ReactNode
}

export function AnnouncementPopupProvider({ children }: Props) {
  const { current, queue, currentIndex, next, prev, finish } = useAnnouncementPopups()

  return (
    <>
      {children}
      {current && queue.length > 0 && (
        <AnnouncementPopupModal
          popup={current}
          currentIndex={currentIndex}
          total={queue.length}
          onNext={next}
          onPrev={prev}
          onFinish={finish}
        />
      )}
    </>
  )
}



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
    <BasicModal open onClose={onFinish} removeButtons={true} setOpen={onFinish}>
      <div className="announcement-popup">

        {/* Header */}
        <h2 className="announcement-popup__title text-center">
          Astuces et nouveautés CommsGPT !
        </h2>

        {/* Body 60/40 */}
        <div className="announcement-popup__content">
          <h3 className="announcement-popup__astuce-title">{popup.title}</h3>
          <div className="announcement-popup__body flex flex-row">

            {/* Image 60% */}
            <div className="announcement-popup__image w-3/5">
              {popup.imageUrl && (
                <img
                  src={popup.imageUrl}
                  alt=""
                  className="w-full h-full object-cover"
                />
              )}
            </div>

            {/* Description 40% */}
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

          {/* Flèche précédent */}
          <button
            onClick={onPrev}
            disabled={isFirst}
            className={`announcement-popup__nav-btn ${isFirst ? 'invisible' : ''}`}
          >
            <span className="material-icons">chevron_left</span>
            <span className="announcement-popup__nav-label">Précédent</span>
          </button>

          {/* Dots */}
          <div className="announcement-popup__dots flex items-center gap-2">
            {Array(total).fill(0).map((_, index) => (
              <span
                key={index}
                className={`
                  announcement-popup__dot rounded-full transition-all duration-200
                  ${index === currentIndex
                    ? 'bg-bnpp-color-green w-3 h-3'
                    : index < currentIndex
                      ? 'bg-bnpp-color-green opacity-50'
                      : 'bg-gray-300 w-2 h-2'
                  }
                `}
              />
            ))}
          </div>

          {/* Suivant ou Terminer */}
          {isLast ? (
            <button
              onClick={onFinish}
              className="announcement-popup__nav-btn announcement-popup__nav-btn--finish"
            >
              <span className="announcement-popup__nav-label">Terminer</span>
            </button>
          ) : (
            <button
              onClick={onNext}
              className="announcement-popup__nav-btn"
            >
              <span className="announcement-popup__nav-label">Suivant</span>
              <span className="material-icons">chevron_right</span>
            </button>
          )}

        </div>

      </div>
    </BasicModal>
  )
}

import { useEffect, useState } from 'react'
import { AnnouncementPopup, dismiss, getVisibleAnnouncementPopups } from '../../core/announcement'

interface UseAnnouncementPopupsReturn {
  current: AnnouncementPopup | null
  queue: AnnouncementPopup[]
  currentIndex: number
  next: () => void
  prev: () => void
  finish: () => void
}

export function useAnnouncementPopups(): UseAnnouncementPopupsReturn {
  const [queue, setQueue] = useState<AnnouncementPopup[]>([])
  const [currentIndex, setCurrentIndex] = useState(0)

  useEffect(() => {
    getVisibleAnnouncementPopups()
      .then((popups) => {
        // Dismiss tout immédiatement au mount
        popups.forEach((p) => dismiss(p.id))
        setQueue(popups)
      })
      .catch(() => setQueue([]))
  }, [])

  const current = queue[currentIndex] ?? null

  const next = () => {
    if (currentIndex < queue.length - 1) {
      setCurrentIndex((i) => i + 1)
    }
  }

  const prev = () => {
    if (currentIndex > 0) {
      setCurrentIndex((i) => i - 1)
    }
  }

  const finish = () => {
    setQueue([])
  }

  return { current, queue, currentIndex, next, prev, finish }
}

