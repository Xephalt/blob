Tu as raison. Le problème vient de ma logique — je dismiss tout au mount avant même que l’user ait vu quoi que ce soit. Il faut dismisser au fur et à mesure que l’user voit chaque popup.
La règle métier correcte :
	∙	Une popup est “vue” dès qu’elle est affichée à l’écran
	∙	Donc on dismiss queue[0] au mount (la première est visible immédiatement)
	∙	On dismiss queue[i] quand on navigue vers elle (next/prev)

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
        // La première popup est visible immédiatement → on la dismiss
        if (popups.length > 0) {
          dismiss(popups[0].id)
        }
      })
      .catch(() => setQueue([]))
  }, [])

  const current = queue[currentIndex] ?? null

  const next = () => {
    if (currentIndex < queue.length - 1) {
      const nextIndex = currentIndex + 1
      // La popup suivante devient visible → on la dismiss
      dismiss(queue[nextIndex].id)
      setCurrentIndex(nextIndex)
    }
  }

  const prev = () => {
    if (currentIndex > 0) {
      // On revient en arrière — déjà vue, déjà dismissée, rien à faire
      setCurrentIndex((i) => i - 1)
    }
  }

  const finish = () => setQueue([])

  return { current, queue, currentIndex, next, prev, finish }
}


Comportement attendu :
	∙	Mount avec 4 popups → dismiss popup[0] → affiche 1/4
	∙	Reload → 3 popups non vues → affiche 1/3 ✅
	∙	Next → dismiss popup[1] → affiche 2/3 (dans la queue figée)
	∙	Reload → 2 popups → 1/2 ✅
	∙	Prev → déjà dismissée → rien à faire, juste navigation UI ✅​​​​​​​​​​​​​​​​