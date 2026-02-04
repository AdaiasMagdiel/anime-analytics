(ns com.adaiasmagdiel.app.components.page-header
  (:require [com.adaiasmagdiel.app.state :as state]
            ["lucide-react" :refer [Search]]))

(defn set-mode [mode]
  (when (not (:thinking @state/app)) 
  	(if (not (some #{mode} ["year" "season"]))
  		(throw (js/Error. "Mode must be one of 'year' or 'season'"))
  		(swap! state/app assoc-in [:filters :mode] mode))))

(defn mode-trigger []
  (let [mode (:mode (:filters @state/app))

        common-style "enabled:cursor-pointer px-4 py-1 rounded-lg  text-sm font-medium shadow-sm disabled:opacity-70 transition-all "
        selected-style "text-white bg-indigo-600"
        unselected-style "text-slate-400 enabled:hover:text-white"]

    [:div {:class "flex bg-black rounded-xl p-2"}
     [:button {:class [common-style
                       (if (= "year" mode) selected-style unselected-style)]
               :on-click #(do
               												(set-mode "year")
               												(swap! state/app assoc-in [:filters :season] ""))
               :disabled (:thinking @state/app)}
      "Yearly"]

     [:button {:class [common-style
                       (if (= "season" mode) selected-style unselected-style)]
               :on-click #(set-mode "season")
               :disabled (:thinking @state/app)}
      "Season"]]))

(defn filters-selector []
  (let [{:keys [mode year season]} (:filters @state/app)

        common-style "overflow-hidden transition-all"
        opened-style "max-w-xs"
        closed-style "max-w-0"

        select-class "bg-black border border-slate-700 text-slate-200 text-sm rounded-xl px-3 py-2 outline-none focus:ring-2 focus:ring-indigo-500 transition-all cursor-pointer capitalize"]

    [:div {:class "flex items-center gap-2"}
     [:div {:class common-style}
      [:select {:class select-class
      										:value year
      										:on-change #(swap! state/app assoc-in [:filters :year] (.. % -target -value))}
       (for [y state/years]
       	^{:key y} [:option y])]]

     [:div {:class [common-style
                    (if (= mode "season") opened-style closed-style)]}
      [:select {:class select-class
      										:value season
      									 :on-change #(swap! state/app assoc-in [:filters :season] (.. % -target -value))}
      	[:option {:value "" :disabled true} "Season"]
       (for [s state/seasons]
       	^{:key s} [:option {:class "capitalize" :value s} s])]]

     [:button {:class "cursor-pointer bg-indigo-600 hover:bg-indigo-500 text-white p-2 rounded-lg transition-all shadow-lg shadow-indigo-500/20 active:scale-95"
               :title "Reload data"}
      [:> Search {:class "w-4 h-4"}]]]))

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

   [:div {:class "w-fit bg-card p-1 px-2 rounded-2xl border border-slate-800 flex flex-col md:flex-row items-center gap-2 shadow-xl"}
    [mode-trigger]
    [:div {:class "h-8 w-[1px] bg-slate-700 mx-2 hidden md:block"}]
    [filters-selector]]])
