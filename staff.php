{
	"roles": [QUT: 
		["org" : QUT, roles : ["learner","orgAdmin","instructor"], "domain": ['*']]


	"roles": [QUT: 
		["org" : QUT, roles : ["learner","orgAdmin","instructor"], "domain": ['*']]

	admin



	orgAdmin - QUT - Do any tasks on within the org
	instructor 
	learner


	content scale
	org
	course
	topic
	question 

	table
	groups
	id, orgID, perm = {"name" : "ACT PEOPLE", domain : [ACT101,ACT202,ACT203]}
	{"name" : "BAB PEOPLE", domain : [ACT101,BAB2001,FIN1001]}



	domain [ACT PEOPLE,BAB PEOPLE]
	domain [ACT1001,BAB1001]


	Assume every group name is unqiue to a org
	Who can change a group?


	"roles": [QUT: ["org" : QUT, roles : ["learner"], "domain": [ACT1001]]

	"roles": [QUT: ["org" : QUT, roles : ["instructor"], "domain": [ACT PEOPLE]]

	"roles": [QUT: ["org" : QUT, roles : ["orgAdmin"], "domain": [ACT PEOPLE,BAB PEOPLE]]

	"roles": [QUT: ["org" : QUT, roles : ["orgAdmin"], "domain": [*]]

}