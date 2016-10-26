Create View [vWebManagementDropDownEmployeeType]
AS
Select FieldDescription, SortSeq from rDropDown where FilterName = 'ddEmployeeType'


/*

select * from rDropDown

Insert Into rDropDown
(
CreatedUserUID, DropDownType, FilterName, SortSeq, FieldDisplay, FieldDescription
)
Values
('User_System_Automation', 'WebManagement', 'ddEmployeeType', 0, 'Employee', 'Employee')
,('User_System_Automation', 'WebManagement', 'ddEmployeeType', 1, 'Contractor', 'Contractor')

Update rDropDown set DropDownUID = [dbo].[CreateUID]('DropDown', DropDownID, 'WEB', getdate()) where DropDownUID is null

*/
