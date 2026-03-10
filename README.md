Le bug du skip : c’est le React.StrictMode dans main.tsx qui exécute les useEffect deux fois en développement. Du coup, au premier render, currentIndex reste à 0 mais la queue se recharge une deuxième fois — et comme dismiss a déjà été appelé, getVisibleAnnouncementPopups filtre les popups déjà dismissées et retourne une queue plus courte.
Fix : initialiser currentIndex à 0 à chaque fois que la queue change, et surtout ne pas dismiss au mount mais à la fermeture.
Voici le hook corrigé :

import { useEffect, useState, useRef } from 'react'
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
  const initialized = useRef(false)

  useEffect(() => {
    if (initialized.current) return
    initialized.current = true

    getVisibleAnnouncementPopups()
      .then((popups) => {
        setQueue(popups)
        // Dismiss tout immédiatement — l'UI est déjà figée
        popups.forEach((p) => dismiss(p.id))
      })
      .catch(() => setQueue([]))
  }, [])

  const current = queue[currentIndex] ?? null

  const next = () => {
    if (currentIndex < queue.length - 1) setCurrentIndex((i) => i + 1)
  }

  const prev = () => {
    if (currentIndex > 0) setCurrentIndex((i) => i - 1)
  }

  const finish = () => setQueue([])

  return { current, queue, currentIndex, next, prev, finish }
}


Le useRef(false) empêche le double-appel du StrictMode. La queue est figée dès le premier fetch, dismiss se fait juste après — l’UI ne réagit pas au localStorage, seulement à l’état local.
Et pour le Modal, remplace les dots et les chevrons :

{/* Footer */}
<div className="announcement-popup__footer flex justify-between items-center mt-4">

  <button
    onClick={onPrev}
    disabled={isFirst}
    className={`btn ${isFirst ? 'invisible' : ''}`}
  >
    <i className="fa fa-chevron-left" />
  </button>

  <span className="text-sm text-gray-500">
    {currentIndex + 1}/{total}
  </span>

  {isLast ? (
    <button onClick={onFinish} className="btn btn--primary">
      Terminer
    </button>
  ) : (
    <button onClick={onNext} className="btn">
      <i className="fa fa-chevron-right" />
    </button>
  )}

</div>
