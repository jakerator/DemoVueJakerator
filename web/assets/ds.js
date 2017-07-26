console.log('App started');




$(function () {
    $('[data-toggle="tooltip"]').tooltip()
})






/*
Main DS Vue.js component
 */
Vue.component('ds-component', {

    template: '#ds-template',
    props: ['dsid'],
    data: function () {

        /*
            Sync some display options with browser's LocalStorage parameters. To keep component state even after browser page refresh
         */
        ls_currentState=JSON.parse(localStorage.getItem(this.dsid+'_currentState'));
        ls_filterGroup=JSON.parse(localStorage.getItem(this.dsid+'_filterGroup'));
        ls_filterText=JSON.parse(localStorage.getItem(this.dsid+'_filterText'));


        return {
            currentState: ( ls_currentState!=''?ls_currentState:{ id:'AZ', 'label': 'List A-Z'} ) ,
            filterGroup: ( ls_filterGroup!=''?ls_filterGroup:null ) ,
            filterText: ( ls_filterText!=''?ls_filterText:null ) ,
            patients: [],
            patientsNextOffset: null,
            archivedPatients: [],
            archivedPatientsNextOffset: null,
            groups: [],
            loadingState: false
        }
    }

    ,
    mounted: function() {
            /*
            Load initial data via REST API
             */
            this.loadPatients(0);
            this.loadArchivedPatients(0);
            this.loadGroups(0);

    },
    updated: function() {
        this.applyJqueryCollapse();

    },
    methods: {
        /*
            Changes current active navigation state
         */
        changeState: function(event) {
            target = event.currentTarget;
            this.currentState={ id: target.dataset.id, label: target.dataset.originalTitle}
            //this.applyJqueryCollapse();

        },

        /*
            Loading patients from API, updating front-end list
            If offset was used ("Load more" clicked), then append patients to the list instead of refreshing
         */
        loadPatients: function (offset ) {
            this.loadingState=true;
            axios.get("/ds/patients" ,
                {
                    params: { filterStatus: 1,
                        offset: offset,
                        filterText: this.filterText,
                        filterGroup: this.filterGroup
                    }
                })
                .then(
                    response => {
                        if (offset>0)
                            {
                            this.patients=this.patients.concat(response.data.patients);
                            }
                        else
                            {
                                this.patients = response.data.patients;
                            }

                        this.patientsNextOffset = response.data.nextOffset;
                        this.loadingState=false;
                    }
                );
        },
        /*
         Loading Archive patients from API, updating front-end list
         */

        loadArchivedPatients: function (offset) {
            this.loadingState=true;
            axios.get("/ds/patients" ,
                {
                    params: { filterStatus: 0,
                        offset: offset
                    }
                })
                .then(
                    response => {
                if (offset>0)
            {
                this.archivedPatients=this.archivedPatients.concat(response.data.patients);
            }
        else
            {
                this.archivedPatients = response.data.patients;
            }

            this.archivedPatientsNextOffset = response.data.nextOffset;
            this.loadingState=false;
        }
        ).catch(function (error) {
                console.log(error.message);
            });
        },

        /*
         Loading Groups from API, updating front-end list
         */
        loadGroups: function (offset) {
            this.loadingState=true;
            axios.get("/ds/groups")
                .then(
                    response => {
                this.groups = response.data.groups;
                this.loadingState=false;
        }
        ).catch(function (error) {
                console.log(error.message);
            });
        },

        /*
         Showing patients from selected group
         */
        showGroup: function (group)
        {
            console.log('Showing group: '+group);
            this.filterGroup=group;
            this.loadPatients(0);
            this.currentState={ id:'AZ', 'label': 'List A-Z'};
        },

        applyJqueryCollapse: function ()
        {
            /*
             Jquery code to apply 'collapseble' native Bootstrap CSS feature to "plus" button.
             Also added some custom code to update "plus/minus" icon
             */

            $('[data-toggle="collapse"]').click(function(e){
                e.preventDefault();
                var target_element= $( '#'+$(this).data('href') );
                $(target_element).collapse('toggle');
                expanded = $(target_element).attr("aria-expanded");
                if (expanded!='true')
                {
                    $(this).find('i').addClass('fa-plus');
                    $(this).find('i').removeClass('fa-minus');
                }
                else
                {
                    $(this).find('i').addClass('fa-minus');
                    $(this).find('i').removeClass('fa-plus');
                }
                return false;
            });
        }



    },
    watch: {

        /*
            Handling text search box. Used 'debounce' trick to avoid backend api rate limits.
            Also updates localstorage with last typed value
         */
        filterText:_.debounce(function (newfilterText) {
            this.filterText=newfilterText;
            this.loadPatients(0,newfilterText)
            /* updating localstorage */
            localStorage.setItem(this.dsid+'_filterText',JSON.stringify(newfilterText));

        }, 500)
        ,
        /*
         Updates localstorage with last CurrentState value
         */
        currentState: function (value) {
            localStorage.setItem(this.dsid+'_currentState',JSON.stringify(value));
        },
        /*
         Updates localstorage with last filterGroup value
         */
        filterGroup: function (value) {
            localStorage.setItem(this.dsid+'_filterGroup',JSON.stringify(value));
        }


    },

})


/*
 patient-item component
 */
Vue.component('patient-item', {

    props: ['patient'],
    template: '#patient-item-template',
    computed: {
        patientRoute: function () {
            return Routing.generate('showPatient', { id: this.patient.id });
        }

    }
})

/*
 group-item component
 */
Vue.component('group-item', {

    props: ['group','filterGroup'],
    template: '#group-item-template',
    methods: {
        setGroup: function (event)
        {
            this.$emit('setgroup',this.group )
        }
    }


})


/*
 Initialize Vue.js on selectet container
 */
var app = new Vue({
    el: '#vueApp',
    data: {

    }

});
