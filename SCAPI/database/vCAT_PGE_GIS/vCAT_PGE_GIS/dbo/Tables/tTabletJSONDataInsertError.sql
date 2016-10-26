CREATE TABLE [dbo].[tTabletJSONDataInsertError] (
    [tJSONDataInsertErrorID] INT                IDENTITY (1, 1) NOT NULL,
    [SvrDTLT]                DATETIME           CONSTRAINT [DF_tTabletJSONDataInsertError_SvrDTLT] DEFAULT (getdate()) NULL,
    [SvrDTLT_Offset]         DATETIMEOFFSET (7) CONSTRAINT [DF_tTabletJSONDataInsertError_SvrDTLT_Offset] DEFAULT (sysdatetimeoffset()) NULL,
    [InsertedData]           VARCHAR (MAX)      NULL,
    [ErrorNumber]            INT                NULL,
    [ErrorMessage]           VARCHAR (500)      NULL
);

