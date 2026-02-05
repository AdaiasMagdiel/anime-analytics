(ns com.adaiasmagdiel.app.components.page-header
  (:require [com.adaiasmagdiel.app.state :as state]
            [com.adaiasmagdiel.app.api :as api]
            ["lucide-react" :refer [RefreshCw]]))

(defn set-mode [mode]
  (when (not (:thinking @state/app))
    (if (not (some #{mode} ["year" "season"]))
      (throw (js/Error. "Mode must be one of 'year' or 'season'"))
      (swap! state/app assoc-in [:filters :mode] mode))))

(defn change-filter [filter value]
  (when (not (:thinking @state/app))
    (swap! state/app assoc-in [:filters filter] value)))

(defn mode-trigger []
  (let [{:keys [thinking filters]} @state/app
        mode (:mode filters)

        common-style "enabled:cursor-pointer px-4 py-1 rounded-lg  text-sm font-medium shadow-sm disabled:opacity-70 transition-all "
        selected-style "enabled:text-white disabled:text-slate-700 enabled:bg-indigo-600 disabled:bg-slate-300"
        unselected-style "text-slate-400 enabled:hover:text-white"]

    [:div {:class "flex bg-black rounded-xl p-2"}
     [:button {:class [common-style
                       (if (= "year" mode)
                         selected-style
                         unselected-style)]
               :on-click #(do
                            (set-mode "year")
                            (change-filter :season "")
                            (api/fetch-analytics))
               :disabled thinking}
      "Yearly"]

     [:button {:class [common-style
                       (if (= "season" mode)
                         selected-style
                         unselected-style)]
               :on-click #(set-mode "season")
               :disabled thinking}
      "Season"]]))

(defn filters-selector []
  (let [{:keys [filters thinking]} @state/app
        {:keys [mode year season]} filters

        div-container-style "overflow-hidden transition-all w-fit"
        div-opened-style "max-w-sm"
        div-closed-style "max-w-0"

        select-class "bg-black border border-slate-700 text-slate-200 text-sm rounded-xl px-3 py-2 outline-none focus:ring-2 focus:ring-indigo-500 transition-all enabled:cursor-pointer capitalize disabled:opacity-70"]

    [:div {:class "flex items-center gap-2"}
     [:div {:class div-container-style}
      [:select {:class select-class
                :value year
                :on-change #(do
                              (change-filter :year (.. % -target -value))
                              (api/fetch-analytics))
                :disabled thinking}
       (for [y state/years]
         ^{:key y} [:option y])]]

     [:div {:class [div-container-style
                    (if (= mode "season") div-opened-style div-closed-style)]}
      [:select {:class select-class
                :value season
                :on-change #(do
                              (change-filter :season (.. % -target -value))
                              (api/fetch-analytics))
                :disabled thinking}
       [:option {:value "" :disabled true} "Season"]
       (for [s state/seasons]
         ^{:key s} [:option {:class "capitalize" :value s} s])]]

     [:div {:class ["overflow-hidden transition-all "
                    (if thinking
                      "scale-100 max-w-[50px] opacity-100"
                      "scale-0 max-w-0 opacity-0")]}
      [:> RefreshCw {:class ["w-4 h-4" (when thinking "animate-spin")]}]]]))

(defn root []
  [:header {:class "flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8"}
   [:div {:class "flex flex-col gap-1"}
    [:div {:class "flex items-center gap-2"}
     [:h1 {:class "font-title text-3xl font-bold text-white tracking-tight"}
      "Insights Overview"]]
    [:p {:class "text-slate-400 text-base leading-relaxed"}
     "Unveiling seasonal patterns and "
     [:span {:class "text-slate-200 font-medium"}
      "industry benchmarks"]]]

   [:div {:class "w-full md:w-fit bg-card p-1 px-2 rounded-2xl border border-slate-800 flex justify-center items-center gap-2 shadow-xl"}
    [mode-trigger]
    [:div {:class "h-8 w-[1px] bg-slate-700 mx-2 hidden md:block"}]
    [filters-selector]]])
