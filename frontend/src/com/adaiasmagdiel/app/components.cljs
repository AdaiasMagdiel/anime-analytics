(ns com.adaiasmagdiel.app.components
  (:require ["lucide-react" :refer [BarChart3 Github]]))

(defn header []
  [:header {:class "sticky top-0 z-50 border-b border-slate-800 bg-dark/50 backdrop-blur-md"}
   [:div {:class "max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"}
    [:nav {:class "flex items-center justify-between h-16"
           :aria-label "Main Navigation"}
     [:a {:href "/"
          :class "flex items-center gap-2 group"
          :title "Home"}
      [:div {:class "bg-indigo-600 p-1.5 rounded-lg group-hover:bg-indigo-500 transition-colors"}
       [:> BarChart3 {:class "text-white w-6 h-6"}]]
      [:span {:class "font-title font-bold text-xl tracking-tight text-white"}
       "Anime"
       [:span {:class "text-indigo-500"}
        "Analytics"]]]

     [:div {:class "flex items-center gap-4 text-sm text-slate-400"}
      [:a {:href "https://github.com/adaiasmagdiel/anime-analytics"
           :target "_blank"
           :rel "noopener noreferrer"
           :class "hover:text-white transition-colors flex items-center gap-2"
           :title "View source on GitHub"}
       [:span {:class "hidden sm:inline"}]
       [:> Github {:class "w-5 h-5"}]]]]]])
